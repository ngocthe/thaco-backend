# Script tự động gen code 
Sử dụng câu lệnh `crud:generator schema.json` để tự động gen: migration, model, request, transformer, service, permission, controller, route cho các api CRUD 

## Tạo file JSON schema
Tại root folder của project, run command sau
```shell
cp schema.json.example schema.json
```
Nội dung của file template sẽ như dưới đây

```json
{
    "posts": {
        "name": {
            "migration": "string:50|index:50",
            "validation": "required|min:5|max:255",
            "filter": "like"
        },
        "status": {
            "migration": "enum:1,2|default:1",
            "validation": "numeric|nullable",
            "filter": "equal"
        },
        "text": {
            "migration": "text",
            "validation": "required"
        },
        "slug": {
            "migration": "string:50|unique"
        },
        "active": {
            "migration": "boolean|default:false",
            "validation": "boolean|nullable",
            "filter": "lt"
        },
        "user_id": {
            "migration": "foreign|nullable|constrained|onDelete",
            "validation": "numeric|exists:App\\Models\\User,id"
        }
    },
    "categories": {
        "name": {
            "migration": "string",
            "validation": "required|min:5|max:255",
            "filter": "like"
        },
        "image": {
            "migration": "string",
            "validation": "required|min:5|max:255"
        }
    }
}
```
Các khóa chính của JSON đại diện cho tên bảng. Đảm bảo tạo chúng theo thứ tự trong trường hợp một bảng có mối quan hệ với bảng khác. Trong trường hợp này, `posts`, `categories` là các bảng của chúng ta.

Tiếp theo, đối với mỗi bảng, hãy xác định các cột của bạn dưới dạng `key` (vì vậy `name`, `status`, `text`, ... trong trường hợp này) và đặt thuộc tính của chúng.

## Migration
Tất cả các thuộc tính của một bảng phải nằm dưới khóa `migration` trong đối tượng json dưới cột liên quan.

Các thuộc tính được phân tách bằng dấu (`|`) và thuộc tính đầu tiên phải luôn là kiểu cột (`string`, `boolean`, `decaimal`, ...).

Các tùy chọn bổ sung (chẳng hạn như độ dài chuỗi) có thể được cung cấp bằng dấu hai chấm (`:`), theo sau là giá trị của tùy chọn.

## Validation
Tất cả các thuộc tính validation phải nằm dưới khóa `validation` trong đối tượng json dưới cột liên quan.
Các giá trị này là trương đương với `laravel validation rules `


## Filter
Với api list, nếu bạn khai báo giá trị `filter` thì script sẽ tự động gen ra đoạn code filter trong service tương ứng của model.

Các filter hỗ trợ:
```
- like
- in
- lt
- gt
- range
- equal
```
Ví dụ:
```php
<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;

class PostService extends BaseService
{
    public function model(): string
    {
        return Post::class;
    }

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        
        if (isset($params['name']) && $this->checkParamFilter($params['name'])) {
            $this->query->where('name', 'LIKE', '%' . $params['name']. '%');
        }
		
        if (isset($params['status']) && $this->checkParamFilter($params['status'])) {
            $this->query->where('status', $params['status']);
        }
		
        if (isset($params['active']) && $this->checkParamFilter($params['active'])) {
            $this->query->where('active', '<=', $params['active']);
        }
		
    }
}

```

## Chạy lệnh 
Sau khi đã chỉnh sửa file schema.json, chạy lệnh dưới đây để tự động gen code:
```
php artisan crud:generator schema.json
```

Câu lệnh sẽ tự động gen ra các migration, request, model, service,... tương ứng với các bảng đã khai báo trong file json.
Ví dụ 1 file migration tạo bảng sẽ như bên dưới:

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string("name", 50)->index();
            $table->enum("state", ['1', '2'])->default('1');
            $table->text("text");
            $table->string("slug", 50)->unique();
            $table->boolean("active")->default(false);
            $table->foreignId("user_id")->nullable(true)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
```
Sau khi code đã được gen ra thì tiến hành check hoặc chỉnh sửa các migration, route cho phù hợp với yêu cầu của api, cuối cùng chạy các lệnh sau để cập nhật migration và permission:

```
php artisan migrate
php artisan db:Seed --class=PermissionSeeder
```
