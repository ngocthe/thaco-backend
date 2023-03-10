# Get started

## 1. Setup environment
### 1.1 Local environment
Cài đặt [docker](https://docs.docker.com/engine/install/) và [docker-compose](https://docs.docker.com/compose/install/)

Tại root folder của project, run các command sau
```shell
git submodule update --init
cp .env.example .env 

cp laradock/.env.example laradock/.env 
```

Edit file `laradock/.env`
```dotenv
PHP_VERSION=7.4

MYSQL_DATABASE=laravel_base
```

Edit file hosts, thêm vào 2 dòng dưới đây
```
127.0.0.1 kaopiz.backend.base
127.0.0.1 kaopiz.admin.base
```

Đây là 2 domain mặc định cho backend và admin. Nếu muốn sử dụng domain khác, thay đổi nội dung file hosts cùng với các biến trong file `.env`
```dotenv
BACKEND_LOCAL_DOMAIN=kaopiz.backend.base
ADMIN_LOCAL_DOMAIN=kaopiz.admin.base
```

`BACKEND_LOCAL_DOMAIN`: backend domain cho client frontend<br>
`ADMIN_LOCAL_DOMAIN`: admin domain cho admin frontend

Cuối dùng run các command sau để deploy stack
```shell
cd laradock
docker-compose up -d nginx mysql redis workspace phpmyadmin
```

**Note**: để run các command như `artisan` hoặc `composer` trên môi trường local, sử dụng các command sau
```shell
./local-bash composer
./local-bash artisan
```

### 1.2 Production, Staging, Dev environment
Codebase design để tách biệt backend và admin server khi deploy lên cloud, 1 server sẽ chỉ serve 1 domain duy nhất, tránh việc client frontend có thể request tới admin api thông qua thay đổi uri.

Trong file `.env` config như sau:

```dotenv
APP_ENV=dev # dev|staging|producion
APP_DOMAIN=backend
```

Config `APP_DOMAIN` để chỉ định serve cho domain nào
* backend: serve backend api
* admin: serve admin api

Ngoài ra còn một số thông tin config liên quan tới database
```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_base
DB_USERNAME=root
DB_PASSWORD=root
```

## 2. Installation
Run các command dưới đây để hoàn tất quá trình setup<br>
**Note**: trên môi trường local, đặt `./local-bash` trước các command
```shell
composer install
php artisan migrate
php artisan passport:install
php artisan passport:client --password --provider admins
php artisan db:seed
```
