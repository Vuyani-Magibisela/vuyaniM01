import os

def parse_structure(lines):
    stack = []
    paths = []

    for line in lines:
        original_line = line
        line = line.rstrip()

        if not line.strip() or line.strip().startswith("#"):
            continue  # Ignore empty lines and comments

        # Remove inline comments
        if "#" in line:
            line = line.split("#")[0].rstrip()

        # Detect if it's a directory (trailing slash or ends with known pattern)
        is_dir = line.strip().endswith("/")

        # Extract actual name
        stripped = line.lstrip("│├└─ ")
        indent_level = (len(line) - len(stripped)) // 4
        name = stripped.strip("/ ")

        if not name:
            continue  # Skip empty names

        # Adjust stack depth
        while len(stack) > indent_level:
            stack.pop()

        stack.append(name)
        path = os.path.join(*stack)
        paths.append((path, is_dir))

    return paths

def create_structure_from_file(filepath, root="."):
    with open(filepath, "r", encoding="utf-8") as f:
        lines = f.readlines()

    paths = parse_structure(lines)

    for path, is_dir in paths:
        full_path = os.path.join(root, path)

        try:
            if is_dir:
                os.makedirs(full_path, exist_ok=True)
            else:
                os.makedirs(os.path.dirname(full_path), exist_ok=True)
                if not os.path.isdir(full_path):  # only create file if it's not a dir
                    open(full_path, 'a').close()
        except IsADirectoryError:
            print(f"⚠️ Skipping directory treated as file: {full_path}")
        except Exception as e:
            print(f"❌ Error with {full_path}: {e}")

    print("✅ Project structure created successfully!")


# Run the script
if __name__ == "__main__":
    create_structure_from_file("tree.txt")
