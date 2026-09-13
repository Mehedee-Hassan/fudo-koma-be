#!/usr/bin/env python3
"""Create private VPS settings once; never replace existing credentials."""
import base64
import os
from pathlib import Path
import secrets

root = Path(__file__).resolve().parent.parent
path = root / '.env.vps'
content = f'''APP_NAME="Follo Cart"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:{base64.b64encode(secrets.token_bytes(32)).decode()}
APP_URL=http://localhost:8080
VPS_HTTP_PORT=8080
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=follo_cart
DB_USERNAME=follo
DB_PASSWORD={secrets.token_hex(24)}
MYSQL_ROOT_PASSWORD={secrets.token_hex(24)}
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=120
# HTTP runs only inside your encrypted SSH tunnel, never on a public port.
SESSION_SECURE_COOKIE=false
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
LOG_CHANNEL=stderr
LOG_LEVEL=info
MAIL_MAILER=log
PUSH_DRIVER=disabled
FIREBASE_PROJECT_ID=
FIREBASE_CREDENTIALS=
CORS_ALLOWED_ORIGINS=http://localhost:3000
'''
try:
    fd = os.open(path, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
except FileExistsError:
    print('.env.vps already exists; kept existing key and passwords.')
else:
    with os.fdopen(fd, 'w') as stream:
        stream.write(content)
    print('Created .env.vps with a unique application key and database passwords.')
