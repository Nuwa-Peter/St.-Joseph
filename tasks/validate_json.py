import json

def validate_json_file():
    """
    Validates the ncdc_pdfs.json file and reports specific errors.
    """
    json_file_path = 'ncdc_pdfs.json'
    print(f"Attempting to validate {json_file_path}...")

    try:
        with open(json_file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        print("JSON is valid!")
        print(f"Successfully loaded {len(data)} entries.")
    except FileNotFoundError:
        print(f"Error: The file {json_file_path} was not found.")
    except json.JSONDecodeError as e:
        print("--- JSON DECODING FAILED ---")
        print(f"Error message: {e.msg}")
        print(f"Line number:   {e.lineno}")
        print(f"Column number: {e.colno}")
        # Let's try to show the problematic line
        try:
            with open(json_file_path, 'r', encoding='utf-8') as f:
                lines = f.readlines()
                if 0 < e.lineno <= len(lines):
                    print("\nProblematic line:")
                    print(f"Line {e.lineno}: {lines[e.lineno - 1].strip()}")
                    # Also show context
                    if e.lineno > 1:
                         print(f"Line {e.lineno-1}: {lines[e.lineno - 2].strip()}")
                    if e.lineno < len(lines):
                         print(f"Line {e.lineno+1}: {lines[e.lineno].strip()}")

        except Exception as read_err:
            print(f"Could not read the file to show context: {read_err}")

    except Exception as ex:
        print(f"An unexpected error occurred: {ex}")

if __name__ == "__main__":
    validate_json_file()
