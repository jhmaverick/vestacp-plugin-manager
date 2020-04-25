#!/usr/bin/env bash

source /usr/local/vesta/plugin-manager/func/helpers.sh

get_plugin_name_from_source() {
    local plugin_dir="$1"

    if [[ -f "$plugin_dir/vestacp.json" ]]; then
        plugin_name="$(get_json_index "name" "$plugin_dir/vestacp.json")"

        if [[ "$plugin_name" && "$plugin_name" != "null" ]]; then
            echo "$plugin_name"
        fi
    fi
}

# Return minimum number if current Vesta version is smaller than necessary
check_vesta_min_version() {
    local plugin_dir="$1"

    if [[ -f "$plugin_dir/vestacp.json" ]]; then
        vesta_version="$(sed -En "s/^VERSION='(.*)'/\1/p" /usr/local/vesta/conf/vesta.conf)"
        min_version="$(get_json_index "min-vesta" "$plugin_dir/vestacp.json")"

        if [[ "$min_version" && "$min_version" != "null" && "$(php -r "echo version_compare(\"$vesta_version\", \"$min_version\", '<') ? '1' : '';")" ]]; then
            echo "$min_version"
        fi
    fi
}

get_from_github() {
    local repo_url="$(echo "$1" | sed -E "s|.git$||")"
    local info="$2"

    local repo_name=""
    local repo_owner=""
    local repo_branch=""

    if [[ "$(echo "$repo_url" | grep -E "^https://github.com/[^/]*/[^/]*/tree/[^/]*$")" ]]; then
        # Get from another branch
        repo_name="$(echo "$repo_url" | sed -En "s|.*/(.*)/tree/.*|\1|p")"
        repo_owner="$(echo "$repo_url" | sed -En "s|.*/(.*)/.*/tree/.*|\1|p")"
        repo_branch="$(basename -- "$repo_url")"
        repo_url="$(echo "$repo_url" | sed -En "s|(.*)/tree/.*|\1|p")"
    elif [[ "$(echo "$repo_url" | grep -E "^https://github.com/[^/]*/[^/]*$")" ]]; then
        # Get from master
        repo_name="$(basename -- "$repo_url")"
        repo_owner="$(echo "$repo_url" | sed -En "s|.*/(.*)/.*|\1|p")"
        repo_branch="master"
    else
        echo "Invalid Github URL" >&2
        return
    fi

    if [[ ! "$(curl -L -I -s "$repo_url" | grep -E "HTTP/(.*)200")" ]]; then
        echo "Github repository not found" >&2
        return
    fi

    if [[ "$info" == "root_url" ]]; then
        echo "$repo_url"
    elif [[ "$info" == "repo_name" ]]; then
        echo "$repo_name"
    elif [[ "$info" == "repo_owner" ]]; then
        echo "$repo_owner"
    elif [[ "$info" == "branch" ]]; then
        echo "$repo_branch"
    elif [[ "$info" == "archive" ]]; then
        if [[ "$(curl -L -I -s "$repo_url/archive/$repo_branch.zip" | grep -E "HTTP/(.*)200")" ]]; then
            echo "$repo_url/archive/$repo_branch.zip"
        fi
    elif [[ "$info" == "raw_path" ]]; then
        echo "https://raw.githubusercontent.com/$repo_owner/$repo_name/$repo_branch"
    elif [[ "$info" ]]; then
        plugin_conf="$(curl "https://raw.githubusercontent.com/$repo_owner/$repo_name/$repo_branch/vestacp.json" 2>/dev/null)"

        if [[ "$(echo "$plugin_conf" | jq -r '.' 2>/dev/null)" ]]; then
            get_json_index "$info" "$plugin_conf"
        fi
    fi
}

# If exist an update return version number
check_update() {
    local plugin_name="$1"
    local new_version_path="$2"

    local new_version=""

    if [[ -f "/usr/local/vesta/plugins/$plugin_name/vestacp.json" ]]; then
        version="$(get_json_index "version" "/usr/local/vesta/plugins/$plugin_name/vestacp.json")"
        plugin_repository="$(get_json_index "repository" "/usr/local/vesta/plugins/$plugin_name/vestacp.json")"

        if [[ "$new_version_path" && -f "$new_version_path/vestacp.json" ]]; then
            new_version="$(get_json_index "version" "$new_version_path/vestacp.json")"
        elif [[ "$(echo "$plugin_repository" | grep -E "^https://github.com/.*")" ]]; then
            new_version="$(get_from_github "$plugin_repository" "version")"
        fi

        if [[ ! "$version" || "$version" == "null" ]] \
            || [[ "$new_version" && "$new_version" != "null" && "$(php -r "echo version_compare(\"$new_version\", \"$version\", '>') ? '1' : '';")" ]]; then
            echo "$new_version"
        fi
    fi
}

install_from_path() {
    local plugin_source="$1"
    local update_if_exist="$2"
    local create_symlink="$3"

    file_name="$(basename -- "$plugin_source")"

    if [[ ! -d "$plugin_source" ]]; then
        echo "Invalid source"
        exit 1
    fi

    plugin_name="$(get_plugin_name_from_source "$plugin_source")"
    min_vesta="$(check_vesta_min_version "$plugin_source")"

    if [[ ! "$plugin_name" ]]; then
        echo "The source is not a Vesta plugin"
        exit 1
    elif [[ "$min_vesta" ]]; then
        echo "The plugin needs VestaCP version $min_vesta or higher."
        exit 1
    elif [[ "${update_if_exist,,}" != "yes" && -d "/usr/local/vesta/plugins/$plugin_name" ]]; then
        echo "There is already a plugin with that name"
        exit 1
    fi

    # Remove old versions
    if [[ -d "/usr/local/vesta/plugins/$plugin_name" ]]; then
        rm -rf "/usr/local/vesta/plugins/$plugin_name"
    fi

    if [[ "${create_symlink,,}" == "yes" ]]; then
        ln -sf "$plugin_source" "/usr/local/vesta/plugins/$plugin_name"
    else
        cp -a "$plugin_source" "/usr/local/vesta/plugins/$plugin_name"
    fi

    configure_plugin "$plugin_name"
}

install_from_zip() {
    local plugin_source="$1"
    local update_if_exist="$2"

    local remove_zip_after_install="no"

    file_name="$(basename -- "$plugin_source" | sed -E "s|.zip$||")"

    # Download zip
    if [[ "$plugin_source" && ! -f "$plugin_source" \
        && "$(curl -L -I -s "$plugin_source" | grep -E "HTTP/(.*)200")" \
        && "$(curl -L -I -s "$plugin_source" | grep -E "Content-Type: application/zip")" ]]; then
        curl -L -J "$plugin_source" -o "/tmp/$file_name.zip"
        plugin_source="/tmp/$file_name.zip"
        remove_zip_after_install="yes"
    fi

    if [[ ! -f "$plugin_source" ]]; then
        echo "Zip not found"
        exit 1
    fi

    # Installation
    local tmp_dir="/tmp/$(random_string 20)"
    rm -rf "$tmp_dir"
    mkdir -p "$tmp_dir"
    unzip "$plugin_source" -d "$tmp_dir"
    if [[ "${remove_zip_after_install,,}" == "yes" ]]; then
        rm -rf "$plugin_source"
    fi

    # Check if files is in a subdirectory
    delete_me=""
    if (( $(ls -1 "$tmp_dir" | wc -l) == 1 )); then
        sub_dir="$tmp_dir/$(ls -1 "$tmp_dir")"

        if [[ -d "$sub_dir" ]]; then
            delete_me="$tmp_dir"
            tmp_dir="$sub_dir"
        fi
    fi

    plugin_name="$(get_plugin_name_from_source "$tmp_dir")"
    min_vesta="$(check_vesta_min_version "$tmp_dir")"

    if [[ ! "$plugin_name" ]]; then
        echo "The source is not a Vesta plugin"
        exit 1
    elif [[ "$min_vesta" ]]; then
        echo "The plugin needs VestaCP version $min_vesta or higher."
        exit 1
    elif [[ "${update_if_exist,,}" != "yes" && -d "/usr/local/vesta/plugins/$plugin_name" ]]; then
        echo "There is already a plugin with that name"
        exit 1
    fi

    # Remove old versions
    if [[ -d "/usr/local/vesta/plugins/$plugin_name" ]]; then
        rm -rf "/usr/local/vesta/plugins/$plugin_name"
    fi

    # Move to plugins dir
    mv "$tmp_dir" "/usr/local/vesta/plugins/$plugin_name"

    # Delete empty dir
    if [[ "$delete_me" ]]; then
        rm -rf "$delete_me"
    fi

    configure_plugin "$plugin_name"
}

# Check installation
#
# * Add in vestacp plugins list
# * Execute hooks
# * Add plugin parts in the vesta environment
configure_plugin() {
    local plugin_name="$1"
    local type_config="install"

    if [[ ! "$plugin_name" ]]; then
        exit 1
    elif [[ ! "$(ls -A "/usr/local/vesta/plugins/$plugin_name")" ]]; then
        rm -rf "/usr/local/vesta/plugins/$plugin_name"
        exit 1
    fi

    # Get plugin data and add installation info
    plugin_data="$( get_json "/usr/local/vesta/plugins/$plugin_name/vestacp.json")"
    plugin_data="$(echo "$plugin_data" | jq -r ".enabled = true | .date = \"$(date +'%Y-%m-%d %H:%M:%S')\"")"

    # Check if plugin exist in vesta list to keep additional configurations
    current_plugin_data="$(get_json_index "$plugin_name" /usr/local/vesta/conf/plugins.json)"
    if [[ "$current_plugin_data" && "$current_plugin_data" != "null" ]]; then
        type_config="update"

        # Merge data
        plugin_data="$(echo "$current_plugin_data $plugin_data" | jq -s ".[0] + .[1]")"
    fi

    # Update vesta plugins list
    update_json_index "$plugin_name" "$plugin_data" /usr/local/vesta/conf/plugins.json

    # Check post_install hook
    if [[ "$type_config" == "install" && -f "/usr/local/vesta/plugins/$plugin_name/hook/post_install" ]]; then
        bash "/usr/local/vesta/plugins/$plugin_name/hook/post_install"
    fi

    # Execute configuration for plugin in the vesta environment
    /usr/local/vesta/plugin-manager/bin/v-rebuild-plugin "$plugin_name"

    # Check if plugin has additional configurations
    if [[ -f "/usr/local/vesta/plugins/$plugin_name/hook/post_enable" ]]; then
        bash "/usr/local/vesta/plugins/$plugin_name/hook/post_enable"
    fi

    echo -e "\nInstallation completed"
}


