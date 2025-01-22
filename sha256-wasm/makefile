all:
	clang --target=wasm32 -nostdlib -Wl,--no-entry -Wl,--export-all -o main.wasm main.c sha256.c