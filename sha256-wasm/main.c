#include "sha256.h"
#include "stdlib.h"

unsigned long strlen(const char * str) {
    unsigned long length = 0;
    while (str[length] != '\0') {
        length++;
    }
    return length;
}

void * memset(void * ptr, int value, unsigned long num) {
    unsigned char *p = ptr;
    while (num--) {
        *p++ = (unsigned char)value;
    }
    return ptr;
}


BYTE hash[32];

BYTE* sha_password(char password[256])
{
    SHA256_CTX ctx;
    
    sha256_init(&ctx);

    sha256_update(&ctx, (BYTE*)password, strlen(password));

    sha256_final(&ctx, hash);

    return hash;
}