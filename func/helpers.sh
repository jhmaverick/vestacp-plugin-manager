#!/usr/bin/env bash

# Generates a random string
#
# 1 - Length
# 2 - Add Special Characters
random_string() {
    length=${1:-16}
    special_characters="${2:-no}"

    matrix='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
    if [[ "${special_characters,,}" == "yes" ]]; then
        matrix+="-._"
    fi

    result=""
    while [ ${n:=1} -le $length ]; do
        result="$result${matrix:$(($RANDOM%${#matrix})):1}"
        let n+=1
    done

    echo "$result"
}

# Get and valid a JSON
#
# * Checks whether it is a valid JSON;
# * Checks if a file has been informed and if the contents of the file are JSON;
# * If you can't get JSON it returns an empty JSON ready to use
#
# 1 - JSON string or JSON file path.
# 2 - (json|array) Default format if file not exist or the content is not a JSON.
#
# Return an empty JSON if file not exist or the content is not a JSON
get_json() {
    local json_source="$1"
    local initial="${2:-json}"

    # Check if file exist and the content is a JSON
    if [[ -f "$json_source" && "$(cat "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
        json="$(cat "$json_source")"
    elif [[ "$(echo "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
        json="$json_source"
    elif [[ "${initial,,}" == "json" ]]; then
        json="{}"
    else
        json="[]"
    fi

    echo "$json"
}

# Get in index in JSON root
#
# 1 - Index
# 2 - JSON string or JSON file path.
#
# Return null if index not exist
get_json_index() {
    local index_name="$1"
    local json_source="$2"

    if [[ "$index_name" ]]; then
        if [[ -f "$json_source" && "$(cat "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
            # Get from file
            json_content="$(cat "$json_source" | jq -r '.' 2>/dev/null)"
        elif [[ "$(echo "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
            # Get from JSON string
            json_content="$json_source"
        else
            json_content="{}"
        fi

        echo "$json_content" | jq -r ".\"$index_name\"" 2>/dev/null
    else
        echo "Invalid arguments" >&2
        echo "Args: <index_name> <json_source>" >&2
    fi
}

# Update index in json root
#
# 1 - Index
# 2 - Value
# 3 - JSON string or JSON file path. If source is a file the content will be updated.
update_json_index() {
    local index_name="$1"
    local index_value="$2"
    local json_source="$3"

    # Check if need add quotes
    if [[ "$index_value" != "true" && "$index_value" != "false" && ! "$(echo "$index_value" | jq -r '.' 2>/dev/null)" ]]; then
        index_value="\"$index_value\""
    fi

    # Check if origin is a JSON string
    if [[ ! "$json_source" || "$(echo "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
        if [[ "$(echo "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
            json_content="$json_source"
        else
            json_content="{}"
        fi

        # Return JSON
        echo "$json_content {\"$index_name\": $index_value}" | jq -s ".[0] + .[1]"
    elif [[ -d "$(dirname "$json_source")" ]]; then
        json_content="$( get_json "$json_source")"

        # Update file content
        echo "$json_content {\"$index_name\": $index_value}" | jq -s ".[0] + .[1]" > "$json_source"
    else
        echo "Invalid arguments or source" >&2
        echo "Args: <index> <value> [<json_source>]" >&2
        echo "" >&2
        echo "The source JSON must be:" >&2
        echo "  * Empty (The index will be added in an empty JSON);" >&2
        echo "  * A JSON string;" >&2
        echo "  * A file (The file will be created if it does not exist. At least the file directory must exist.)." >&2
    fi
}

# Delete index in json root
#
# 1 - Index
# 2 - JSON string or JSON file path. If source is a file the content will be updated.
delete_json_index() {
    local index_name="$1"
    local json_source="$2"

    # Check if origin is a JSON string
    if [[ "$index_name" && "$(echo "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
        # Return JSON
        echo "$json_source" | jq -r "del(.\"$index_name\")"
    elif [[ "$index_name" && -f "$json_source" && "$(cat "$json_source" | jq -r '.' 2>/dev/null)" ]]; then
        # Update file content
        json_content="$( get_json "$json_source")"
        echo "$json_content" | jq -r "del(.\"$index_name\")" > "$json_source"
    else
        echo "Invalid arguments" >&2
        echo "Args: <index_name> <json_source>" >&2
    fi
}

# Convert multidimensional json to another format
#
# 1 - (plain|shell|csv) Format
# 2 - JSON string or JSON file path. If source is a file the content will be updated.
# 3 - Indexes. Ex: name|ip|date
json_to() {
    local format="$1"
    local json_source="$2"
    local columns="$3"
    columns=(${columns//|/ })

    if [[ ! "$json_source" ]]; then
        echo "Invalid arguments" >&2
        echo "Args: <json_source>" >&2
        return
    fi

    case "$format" in
        plain)  separator="|" ;;
        shell)  separator="|" ;;
        csv)    separator="," ;;
        *)      return ;;
    esac

    result=""

    # Header
    if [[ "${format,,}" == "shell" || "${format,,}" == "csv" ]]; then
        # Show header
        header=""
        header_separator=""

        for col_name in "${columns[@]}"; do
            if [[ "$header" ]]; then
                header+="$separator"
                header_separator+="$separator"
            fi

            header+="${col_name^^}"
            header_separator+="$(echo "$col_name" | sed -Ee "s/./-/g")"
        done

        result="$header"

        if [[ "${format,,}" == "shell" ]]; then
            result+="\n$header_separator"
        fi
    fi

    # Lines
    for ln in $(get_json "$json_source" "[]" | jq -r '.[] | @base64'); do
        ln="$(echo "$ln" | base64 --decode)"

        ln_values=""
        for col_name in "${columns[@]}"; do
            if [[ "$ln_values" ]]; then
                ln_values+="$separator"
            fi

            value="$(echo "$ln" | jq -r ".\"$col_name\"")"
            if [[ "${format,,}" == "csv" ]]; then
                value="\"${value//\"/\"\"}\""
            elif [[ ! "$value" ]]; then
                value="null"
            fi

            ln_values+="$value"
        done

        if [[ "$result" ]]; then
            result+="\n"
        fi

        result+="$ln_values"
    done

    if [[ "${format,,}" == "shell" || "${format,,}" == "plain" ]]; then
        echo -e "$result" | column -t -s "$separator"
    else
        echo -e "$result"
    fi
}
