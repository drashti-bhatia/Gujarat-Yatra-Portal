import os
from PIL import Image

# Configuration
MAX_SIZE_KB = 64
MAX_SIZE_BYTES = MAX_SIZE_KB * 1024

# Supported image formats (including .webp)
SUPPORTED_FORMATS = ('.jpg', '.jpeg', '.png', '.webp')

def compress_image(input_path):
    try:
        img = Image.open(input_path)

        # Convert to RGB if needed
        if img.mode in ("RGBA", "P"):
            img = img.convert("RGB")

        ext = os.path.splitext(input_path)[1].lower()
        quality = 95
        step = 5

        # Temporary path to avoid corrupting original file if something fails
        temp_path = input_path + ".temp"

        while quality > 5:
            img.save(temp_path, format=img.format, optimize=True, quality=quality)

            if os.path.getsize(temp_path) <= MAX_SIZE_BYTES:
                os.replace(temp_path, input_path)  # Overwrite original
                print(f"[✔] Compressed: {input_path} ({os.path.getsize(input_path)//1024} KB)")
                return

            quality -= step

        os.remove(temp_path)
        print(f"[✘] Failed to compress {input_path} below {MAX_SIZE_KB} KB. Final size unchanged.")

    except Exception as e:
        print(f"[!] Error compressing {input_path}: {e}")

def compress_all_images(root_folder='.'):
    for dirpath, _, filenames in os.walk(root_folder):
        for filename in filenames:
            if filename.lower().endswith(SUPPORTED_FORMATS):
                file_path = os.path.join(dirpath, filename)
                compress_image(file_path)

if __name__ == '__main__':
    compress_all_images()
