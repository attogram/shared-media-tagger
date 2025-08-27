# Shared Media Tagger v2 (SMT2) - Laravel Conversion Guide

This document provides a step-by-step guide for converting the Shared Media Tagger application to the Laravel framework.

## 1. Project Setup

These steps will guide you through setting up a new Laravel project within the existing repository, keeping the original source code untouched for reference.

### 1.1. Create a New Laravel Project

It is recommended to create a new `laravel` directory to house the new application. This can be done using Composer.

```bash
composer create-project laravel/laravel laravel
```

If you do not have Composer installed locally, you can use a Docker container to run Composer commands. Create a `docker-compose.yml` file in the root of the project with the following content:

```yaml
services:
  workspace:
    image: composer:latest
    volumes:
      - ./:/app
    working_dir: /app
    tty: true
    stdin_open: true
```

Then, run the following command to create the Laravel project:

```bash
docker compose run --rm workspace composer create-project laravel/laravel laravel
```

### 1.2. Configure Environment

After creating the project, navigate to the `laravel` directory and create a `.env` file by copying the `.env.example` file.

```bash
cd laravel
cp .env.example .env
```

Generate a new application key:

```bash
php artisan key:generate
```

(If using Docker, run `docker compose run --rm workspace php artisan key:generate` from the root directory, after changing the `working_dir` in `docker-compose.yml` to `/app/laravel`)

### 1.3. Database Setup

This guide will use SQLite for the database. To configure this, edit the `.env` file in the `laravel` directory and make the following changes:

```dotenv
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

Create an empty SQLite database file in the `laravel/database` directory:

```bash
touch laravel/database/database.sqlite
```

## 2. Database Migrations

Next, create the database migrations to define the schema for the application.

### 2.1. Create Migration Files

Run the following Artisan commands from within the `laravel` directory to create the migration files:

```bash
php artisan make:migration create_anonymous_users_table
php artisan make:migration create_block_table
php artisan make:migration create_topics_table
php artisan make:migration create_topic_media_table
php artisan make:migration create_media_table
php artisan make:migration create_site_table
php artisan make:migration create_tags_table
php artisan make:migration create_taggings_table
```

### 2.2. Modify Existing Migrations

The default Laravel installation includes migrations for `users`, `cache`, and `jobs`. The `users` table migration should be kept for admin users. The other migrations can be left as they are.

### 2.3. Add Migration Code

Edit the newly created migration files in `laravel/database/migrations` and add the following code.

#### `create_anonymous_users_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anonymous_users', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip')->nullable();
            $table->string('host')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('last')->nullable();
            $table->timestamps();
            $table->unique(['ip', 'host', 'user_agent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymous_users');
    }
};
```

#### `create_block_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block', function (Blueprint $table) {
            $table->integer('pageid')->primary();
            $table->text('title')->nullable();
            $table->text('thumb')->nullable();
            $table->integer('ns')->nullable();
            $table->timestamp('updated')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block');
    }
};
```

#### `create_topics_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('curated')->default(0);
            $table->integer('pageid')->nullable();
            $table->integer('files')->nullable();
            $table->integer('subcats')->nullable();
            $table->integer('local_files')->default(0);
            $table->integer('curated_files')->default(0);
            $table->integer('missing')->default(0);
            $table->integer('hidden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
```

#### `create_topic_media_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->integer('media_pageid');
            $table->timestamps();
            $table->unique(['topic_id', 'media_pageid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_media');
    }
};
```

#### `create_media_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->integer('pageid')->primary();
            $table->boolean('curated')->default(0);
            $table->text('title')->nullable();
            $table->text('url')->nullable();
            $table->text('descriptionurl')->nullable();
            $table->text('descriptionshorturl')->nullable();
            $table->text('imagedescription')->nullable();
            $table->text('artist')->nullable();
            $table->text('datetimeoriginal')->nullable();
            $table->text('licenseuri')->nullable();
            $table->text('licensename')->nullable();
            $table->text('licenseshortname')->nullable();
            $table->text('usageterms')->nullable();
            $table->text('attributionrequired')->nullable();
            $table->text('restrictions')->nullable();
            $table->integer('size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('sha1')->nullable();
            $table->string('mime')->nullable();
            $table->text('thumburl')->nullable();
            $table->integer('thumbwidth')->nullable();
            $table->integer('thumbheight')->nullable();
            $table->string('thumbmime')->nullable();
            $table->string('user')->nullable();
            $table->integer('userid')->nullable();
            $table->float('duration')->nullable();
            $table->string('timestamp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
```

#### `create_site_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('about')->nullable();
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->boolean('curation')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site');
    }
};
```

#### `create_tags_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->nullable();
            $table->integer('score')->nullable();
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
```

#### `create_taggings_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taggings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anonymous_user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->integer('media_pageid');
            $table->timestamps();
            $table->unique(['anonymous_user_id', 'tag_id', 'media_pageid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggings');
    }
};
```

### 2.4. Run the Migrations

Once all the migration files are created and edited, run the following command from the `laravel` directory to create the database tables:

```bash
php artisan migrate
```

## 3. Eloquent Models

Next, create the Eloquent models for each database table.

### 3.1. Create Model Files

Run the following Artisan commands from within the `laravel` directory to create the model files:

```bash
php artisan make:model AnonymousUser
php artisan make:model Block
php artisan make:model Topic
php artisan make:model Media
php artisan make:model Site
php artisan make:model Tag
```

### 3.2. Add Model Code

Edit the newly created model files in `laravel/app/Models` and add the following code.

#### `AnonymousUser.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonymousUser extends Model
{
    use HasFactory;

    protected $table = 'anonymous_users';

    protected $fillable = [
        'ip',
        'host',
        'user_agent',
        'last',
    ];
}
```

#### `Block.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $table = 'block';
    protected $primaryKey = 'pageid';
    public $incrementing = false;

    protected $fillable = [
        'pageid',
        'title',
        'thumb',
        'ns',
    ];
}
```

#### `Topic.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'curated',
        'pageid',
        'files',
        'subcats',
        'local_files',
        'curated_files',
        'missing',
        'hidden',
    ];

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'topic_media', 'topic_id', 'media_pageid');
    }
}
```

## 6. View Templates

Next, create the Blade view templates for the application.

### 6.1. Create Layout File

First, create a main layout file in `laravel/resources/views/layouts/app.blade.php`. This file will contain the common HTML header and footer for all pages.

#### `layouts/app.blade.php`

```blade
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
@if(config('app.use_cdn'))
<link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css"
      integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B"
      crossorigin="anonymous">
@else
<link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
@endif
<style>
body { background-color:#343a40; }
a { text-decoration:none !important; color:darkblue; }
a:hover { background-color:yellow; color:black !important; }
.hovery:hover { background-color:yellow !important; color:black !important; }
.nohover:hover { background-color:inherit; color:inherit; }
.mediatitle { font-size:80%; }
.attribution { font-size:65%; }
</style>
@if(config('app.use_cdn'))
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"
integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
@else
<script src="{{ asset('jquery.min.js') }}"></script>
<script src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
@endif
<title>{{ $title ?? 'Shared Media Tagger' }}</title>
</head>
<body>
<div class="container-fluid">
@isset($customSiteHeader)
<div class="row"><div class="col">{!! $customSiteHeader !!}</div></div>
@endisset

@yield('content')

<div class="row bg-dark text-right text-secondary mt-1 mb-3 small">
    <div class="col">
        Powered by <a class="text-white-50" target="c" href="https://github.com/attogram/shared-media-tagger">Shared Media Tagger v2.0.0</a>
        <br />
        Hosted by <a class="text-white-50" href="//{{ request()->getHost() }}">{{ request()->getHost() }}</a>
    </div>
</div>
@isset($customSiteFooter)
<div class="row"><div class="col">{!! $customSiteFooter !!}</div></div>
@endisset
</div>
</body>
</html>
```

### 6.2. Create Partials

Create reusable partial views for components like the menu.

#### `partials/menu.blade.php`

```blade
<div class="row text-white bg-dark small pb-2 pt-2">
    <div class="col-7 col-sm-5 text-left">
        <a class="text-white" href="{{ route('home') }}">{{ config('app.name', 'Shared Media Tagger') }}</a>
        @auth
            <a class="text-white" href="{{ route('user.me') }}">
                <span class="ml-2">
                    {{-- TODO: Get user score --}}
                </span>
                <span class="d-none d-md-inline mr-2">
                    completed
                </span>
            </a>
            <a class="text-white ml-3" href="{{ route('admin.home') }}" title="Admin">🔧</a>
        @endauth
    </div>
    <div class="col-5 col-sm-7 text-right">
        <a class="text-white pr-1 pl-1" href="{{ route('random') }}" title="Random File">
            ▷ <div class="d-none d-sm-inline mr-2">Random</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="{{ route('search') }}" title="Search">
            ⧂
            <div class="d-none d-sm-inline mr-2">Search</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="{{ route('browse') }}" title="All Files">
            ⊞
            <div class="d-none d-md-inline">{{-- TODO: Get file count --}}</div>
            <div class="d-none d-sm-inline mr-2">Files</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="{{ route('topics') }}" title="Topics">
            ⋈
            <div class="d-none d-md-inline">{{-- TODO: Get topics count --}}</div>
            <div class="d-none d-sm-inline mr-2">Topics</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="{{ route('scores') }}" title="Scores">
            ⊜
            <div class="d-none d-md-inline">{{-- TODO: Get total votes count --}}</div>
            <div class="d-none d-sm-inline">Votes</div>
        </a>
    </div>
</div>
```

### 6.3. Create Page Views

Create the main view files for each page.

#### `home.blade.php`

```blade
@extends('layouts.app')

@section('content')
@include('partials.menu')
<div class="row bg-white">
    <div class="col-6">
        <p>
            {{ $about }}
        </p>
        <p>
            <a href="{{ route('random') }}"><kbd>▷</kbd></a>
            <a href="{{ route('random') }}" class="font-italic">Random File</a>
        </p>
        <p>
            <a href="{{ route('scores') }}"><kbd>⊜</kbd></a>
            <a href="{{ route('scores') }}">{{ $countVotes }} Votes</a>
        </p>
        <p>
            <a href="{{ route('browse') }}"><kbd>⊞</kbd></a>
            <a href="{{ route('browse') }}">{{ $countFiles }} Files</a>
        </p>
        <p>
            <a href="{{ route('topics') }}"><kbd>⋈</kbd></a>
            <a href="{{ route('topics') }}">{{ $countTopics }} Topics</a>
        </p>
        <form method="GET" action="{{ route('search') }}">
        <p>
            <input type="text" name="q" size="15" maxlength="256" value="{{ $query ?? '' }}" />
            <input type="submit" value=" ⧂ Search " />
        </p>
        </form>
    </div>
    <div class="col-6">
        @foreach ($random as $media)
            @include('partials.thumbnail', ['media' => $media])
        @endforeach
    </div>
</div>
@endsection
```

#### `admin/add.blade.php`

```blade
@extends('layouts.app')

@section('content')
@include('partials.menu')
@include('admin.partials.menu')
<form>
    <div class="row bg-info pt-3 pb-2">
        <div class="col-8">
            <input class="form-control" id="q" name="q" type="text" value="{{ $query }}">
        </div>
        <div class="col-4">
            <button type="submit" name="t" value="topics"
                    class="btn btn-dark float-left m-1">Topics</button>
            <button type="submit" name="t" value="media"
                    class="btn btn-dark foat-left m-1">Media</button>
        </div>
    </div>
</form>
@if (count($results))
    @if ($type == 'topics')
        @include('admin.partials.add_topics', ['results' => $results])
    @elseif ($type == 'media')
        @include('admin.partials.add_media', ['results' => $results])
    @endif
@endif
@endsection
```

#### `admin/partials/add_topics.blade.php`

```blade
<form action="{{ route('admin.add.store') }}" method="POST">
@csrf
<div class="row bg-info text-center">
<div class="col">
    <button type="submit" class="btn btn-dark mt-3 mb-3 ml-2 mr-2">Import checked Topics</button>
</div>
</div>
<div class="row bg-info text-white small font-italic">
    <div class="col-12 col-sm-6 font-weight-bold">Topic</div>
    <div class="col-4 col-sm-2">Add</div>
    <div class="col-4 col-sm-2">Add Media</div>
    <div class="col-4 col-sm-2">Add Subcats</div>
</div>
@foreach ($results as $topic)
<div class="row border border-light bg-white hovery">
    <div class="col-12 col-sm-6 font-weight-bold">
        <a target="commons" href="https://commons.wikimedia.org/wiki/{{ urlencode($topic['title']) }}">{{ str_replace('Category:', '', $topic['title']) }}</a>
    </div>
    <div class="col-4 col-sm-2">
        <div class="form-check form-check-inline font-weight-bold">
            <input type="checkbox" name="ti[]" value="{{ $topic['pageid'] }}" />
        </div>
    </div>
    <div class="col-4 col-sm-2">
        @if (!empty($topic['files']))
        <div class="form-check form-check-inline">
            <input type="checkbox" name="tm[]" value="{{ $topic['pageid'] }}" />
            {{ $topic['files'] }}
        </div>
        @endif
    </div>
    <div class="col-4 col-sm-2">
        @if (!empty($topic['subcats']))
        <div class="form-check form-check-inline">
            <input type="checkbox" name="ts[]" value="{{ $topic['pageid'] }}" />
            {{ $topic['subcats'] }}
        </div>
        @endif
    </div>
</div>
@endforeach
<div class="row bg-info text-white small font-italic">
    <div class="col-12 col-sm-6 font-weight-bold">Topic</div>
    <div class="col-4 col-sm-2">Add</div>
    <div class="col-4 col-sm-2">Add Media</div>
    <div class="col-4 col-sm-2">Add Subcats</div>
</div>
<div class="row bg-info text-center">
    <div class="col">
        <button type="submit" class="btn btn-dark mt-3 mb-3 ml-2 mr-2">Import checked Topics</button>
    </div>
</div>
</form>
```

#### `admin/partials/thumbnail_curate.blade.php`

```blade
@php
    // TODO: Replicate this logic
    // $thumb = $this->getThumbnail($data);
    $thumb = ['url' => $media['thumburl'], 'width' => $media['thumbwidth'], 'height' => $media['thumbheight']];
@endphp
<div class="d-inline-block align-top text-center">
    <input type="checkbox" name="m[]" value="{{ $media['pageid'] }}" class="form-check-input" />
    <br />
    <a target="commons"
       href="{{ $media['descriptionurl'] }}"><img
            class="border"
            src="{{ $thumb['url'] }}"
            width="{{ $thumb['width'] }}"
            height="{{ $thumb['height'] }}"
            title="{{ $media['pageid'] . ' - ' . $media['width'] . ' x ' . $media['height'] . ' px' . ' - ' . $media['mime'] . ' - ' . $media['size'] . ' bytes' . "\n" . $media['title'] . "\n" . $media['imagedescription'] }}" /></a>
    <div style="font-size:65%;">
        {{-- TODO: getMediaName --}}
        {{ Str::limit($media['title'], 27) }}
    </div>
</div>
```

#### `admin/partials/add_media.blade.php`

```blade
<form action="{{ route('admin.add.store') }}" method="POST">
@csrf
<div class="row bg-white pb-3">
    <div class="col">
        @foreach ($results as $media)
            @include('admin.partials.thumbnail_curate', ['media' => $media])
        @endforeach
    </div>
</div>
<div class="row bg-info text-center">
    <div class="col">
        <button type="submit" class="btn btn-dark mt-3 mb-3 ml-2 mr-2">Import checked Media</button>
    </div>
</div>
</form>
```

#### `admin/curate.blade.php`

```blade
@extends('layouts.app')

@section('content')
@include('partials.menu')
@include('admin.partials.menu')

<style>
.curation_container { background-color:#ddd; color:black; padding:10px; display:flex; flex-wrap:wrap; }
.curation_container img { margin:1px; }
.curation_keep { border:12px solid green; }
.curation_delete { border:12px solid red; }
.curation_que { border:12px solid grey; }
</style>
<div class="row bg-secondary">
    <div class="col mb-4">
        <form name="media" action="{{ route('admin.curate.batch') }}" method="POST">
            @csrf
            @php
                $menu = '<div class="bg-info p-1">'
                    . '<input type="submit" value="          Curate Marked Files        " />'
                    . ' <span style="display:inline-block; font-size:90%;">Mark ALL '
                    . ' <a href="javascript:mark_all_keep();">[KEEP]</a>'
                    . ' <a href="javascript:mark_all_delete();">[DELETE]</a>'
                    . ' <a href="javascript:mark_all_que();">[QUE]</a></span>'
                    . ' - <a href="' . route('admin.curate', ['l' => $pageLimit]) . '">' . $pageLimit . '</a> of '
                    . number_format($uncuratedCount) . ' in que'
                    . '</div>';
            @endphp
            {!! $menu !!}
            <div class="curation_container">
            @foreach ($medias as $media)
                @php
                    $thumb = ['url' => $media->thumburl, 'width' => $media->thumbwidth, 'height' => $media->thumbheight];
                    $pageid = $media->pageid;
                    $imgInfo = print_r($media->toArray(), true);
                @endphp
                <div>
                    <a target="site" style="font-size:10pt; text-align:center;" href="{{ route('info', $pageid) }}">{{ $pageid }}</a><br />
                    <img name="{{ $pageid }}" id="{{ $pageid }}"  src="{{ $thumb['url'] }}"
                         width="{{ $thumb['width'] }}" height="{{ $thumb['height'] }}" title="{{ $imgInfo }}"
                         onclick="curation_click(this.id);" class="curation_que">
                </div>
                <input style="display:none;" type="checkbox" name="keep[]" id="keep{{ $pageid }}" value="{{ $pageid }}">
                <input style="display:none;" type="checkbox" name="delete[]" id="delete{{ $pageid }}" value="{{ $pageid }}">
            @endforeach
            </div>
            <br />
            {!! $menu !!}
        </form>
    </div>
</div>
@push('scripts')
<script>
function mark_all_keep() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', true);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', false);
    });
    $("img").each( function() {
        $(this).prop('class','curation_keep');
    });
}
function mark_all_delete() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', false);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', true);
    });
    $("img").each( function() {
        $(this).prop('class','curation_delete');
    });
}
function mark_all_que() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', false);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', false);
    });
    $("img").each( function() {
        $(this).prop('class','curation_que');
    });
}
function curation_click(pageid) {
    var media = $('#' + pageid);
    var media_keep = $('#keep' + pageid);
    var media_delete = $('#delete' + pageid);
    switch( media.prop('class') ) {
        case 'curation_que':
            media.prop('class', 'curation_delete');
            media_keep.prop('checked', false);
            media_delete.prop('checked', true);
            return;
        case 'curation_delete':
            media.prop('class', 'curation_keep');
            media_keep.prop('checked', true);
            media_delete.prop('checked', false);
            return;
        case 'curation_keep':
            media.prop('class', 'curation_que');
            media_keep.prop('checked', false);
            media_delete.prop('checked', false);
            return;
    }
}
</script>
@endpush
@endsection
```

## 7. Finalization

The final steps involve setting up authentication, installing dependencies, and moving static assets.

### 7.1. Authentication

To add authentication to the application, install the Laravel UI package.

```bash
composer require laravel/ui
php artisan ui bootstrap --auth
npm install && npm run dev
```

This will create the necessary authentication views and routes.

### 7.2. Dependencies

The application depends on the `attogram/shared-media-api` package. Install it using Composer:

```bash
composer require attogram/shared-media-api
```

### 7.3. Static Assets

Copy the static assets from the old project's `public` directory to the `laravel/public` directory.

```bash
cp public/jquery.min.js laravel/public/js/
# Copy any other assets, like bootstrap if not using CDN
```

#### `admin/partials/menu.blade.php`

```blade
<div class="row bg-secondary p-1">
    <div class="col">
        <a class="text-white mr-2" href="{{ route('admin.home') }}">🔧</a>
        <a class="text-white mr-2" href="{{ route('admin.add') }}">ADD</a>
        <a class="text-white mr-2" href="{{ route('admin.site') }}">SITE</a>
        <a class="text-white mr-2" href="{{ route('admin.tag') }}">TAGS</a>
        <a class="text-white mr-2" href="{{ route('admin.topic') }}">TOPICS</a>
        <a class="text-white mr-2" href="{{ route('admin.media') }}">MEDIA</a>
        <a class="text-white mr-2" href="{{ route('admin.curate') }}">CURATE</a>
        <a class="text-white mr-2" href="{{ route('admin.user') }}">USERS</a>
        <a class="text-white mr-2" href="{{ route('admin.database') }}">DATABASE</a>
        <a class="text-white font-italic" href="{{ route('logout') }}">logout</a>
    </div>
</div>
```

#### `partials/image.blade.php`

```blade
<img class="img-fluid"
     src="{{ $media->displayUrl }}"
     width="{{ $media->thumbwidth }}"
     height="{{ $media->thumbheight }}"
     alt="">
<div style="font-size:70%;">
    &copy; {{-- TODO: getArtistName --}}
    /
    {{-- TODO: getLicenseName --}}
</div>
```

### 6.4. Create Admin Views

Create the view files for the admin section.

#### `admin/home.blade.php`

```blade
@extends('layouts.app')

@section('content')
@include('partials.menu')
@include('admin.partials.menu')
<div class="row bg-white">
    <div class="col">
    Settings:
    <ul>
        <li>Site Name: <kbd>{{ config('app.name') }}</kbd></li>
        <li>Curation Mode: <kbd>{{-- TODO: Curation Mode --}}</kbd></li>
        <li>Site Url: <kbd>{{ url('/') }}</kbd></li>
        <li>Protocol: <kbd>{{ request()->getScheme() }}</kbd></li>
        <li>Server: <kbd>{{ request()->getHost() }}</kbd></li>
    </ul>
    <hr />
    Directories:
    <ul>
        <li>cwd: <kbd>{{ getcwd() }}</kbd></li>
        <li>sourceDirectory: <kbd>{{ base_path() }}</kbd></li>
        <li>databaseDirectory: <kbd>{{ database_path() }}</kbd></li>
        <li>adminConfigFile: <kbd>{{-- TODO: adminConfigFile --}}</kbd></li>
    </ul>
    <hr />
    Discovery:
    <ul>
        <li><a href="{{ route('sitemap') }}">sitemap.xml</a></li>
        <li>/public/.htaccess:
            {{ is_readable(public_path('.htaccess')) ? '✅ACTIVE' : '❌MISSING' }}
        </li>
        <li><a href="/robots.txt">/robots.txt</a>:
            <span style="font-family:monospace;">{{-- TODO: checkRobotstxt --}}</span>
        </li>

    </ul>

    <hr />
    About Shared Media Tagger:
    <ul>
        <li><a target="c"
                        href="https://github.com/attogram/shared-media-tagger">Github: attogram/shared-media-tagger</a></li>
        <li><a target="c"
               href="https://github.com/attogram/shared-media-tagger/blob/master/README.md">README</a></li>
        <li><a target="c"
               href="https://github.com/attogram/shared-media-tagger/blob/master/LICENSE.md">LICENSE</a></li>
    </ul>
    </div>
</div>
@endsection
```

#### `topic.blade.php`

```blade
@extends('layouts.app')

@section('content')
@include('partials.menu')
<div class="row bg-white">
    <div class="col">

    <div style="float:right; padding:0 20px 4px 0; font-size:80%;">
        {{-- TODO: votesPerTopic --}}
    </div>
    <h1>{{ str_replace('Category:', '', $topic->name) }}</h1>
    <b>{{ $medias->total() }}</b> files
    {{ $medias->links() }}
    <br clear="all" />
    @auth
        <form action="{{ route('admin.media.multi-delete') }}" method="POST" name="media">
        @csrf
    @endauth

    @foreach ($medias as $media)
        @include('partials.thumbnail', ['media' => $media])
    @endforeach

    {{ $medias->links() }}

    @auth
        {{-- TODO: includeAdminTopicFunctions --}}
        </form>
    @endauth
    </div>
</div>
@endsection
```

#### `info.blade.php`

```blade
@extends('layouts.app')

@section('content')
@include('partials.menu')
<div class="row">
    <div class="col-sm-7 text-center align-top bg-secondary">
        {{-- TODO: includeTags --}}

        @if(in_array($media->mime, config('app.mime_types_video')))
            {{-- TODO: displayVideo --}}
        @elseif(in_array($media->mime, config('app.mime_types_audio')))
            {{-- TODO: displayAudio --}}
        @else
            @include('partials.image', ['media' => $media])
        @endif

        {{-- TODO: includeAdminMediaFunctions --}}
    </div>
    <div class="col-sm-5 bg-white align-top">
        @if ($media->imagedescriptionRows > 4)
        <textarea class="h2" readonly rows="{{ $media->imagedescriptionRows }}"
            style="width:100%;font-size:130%; font-weight:bold;">{{
            $media->imagedescriptionSafe
        }}</textarea>
        @else
        <h2>{{ $media->imagedescriptionSafe }}</h2>
        @endif
        <dl>
            <dt>Scoring:</dt>
            <dd>{{-- TODO: displayVotes --}}</dd>
        </dl>
        <p>
            <em>Topics:</em>
            <br />
            {{-- TODO: displayTopics --}}
        </p>
        <em>Download:</em>
        <ul>
            <li>
                <small>Source:</small>
                <a target="c"
                   href="{{ $media->descriptionurl }}">commons.wikimedia.org</a>
                    # <a target="c" href="{{ $media->descriptionshorturl }}">{{ $media->pageid }}</a>
            </li>
            <li><small>Filename:</small> <b>{{ str_replace('File:', '', $media->title) }}</b></li>
            @php
                // TODO: getThumbnail
                $thumb = ['url' => $media->thumburl, 'width' => $media->thumbwidth, 'height' => $media->thumbheight];
            @endphp
            <li><small>Thumbnail:</small> <a target="c" href="{{ $thumb['url'] }}"
                >{{ $thumb['width'] }}x{{ $thumb['height'] }} pixels
                - {{ $media->thumbmime }}</a>
            </li>
            <li><small>Preview:</small> <a target="c" href="{{ $media->thumburl }}"
                >{{ $media->thumbwidth }}x{{ $media->thumbheight }} pixels
                - {{ $media->thumbmime }}
                </a></li>
            <li><small>Full size:</small> <a target="c" href="{{ $media->url }}"
                >{{ $media->width }}x{{ $media->height }} pixels
                - {{ $media->mime }}
                - {{ number_format($media->size) }} bytes
                </a></li>
        @if ($media->duration > 0)
            <li><small>Duration:</small> {{-- TODO: secondsToTime --}}</li>
        @endif
        </ul>
        <p>
            <em>Licensing:</em>
            <ul>
            <li><small>Artist:</small> {{ $media->artist ? strip_tags($media->artist) : 'unknown' }}</li>
            @if ($media->licenseuri && $media->licenseuri != 'false')
                <li><small>License:</small> <a target="license" href="{{ $media->licenseuri }}">{{ implode(', ', $media->licensing) }}</a></li>
            @else
                <li><small>License:</small> {{ implode(', ', $media->licensing) }}</li>
            @endif
            @if ($media->attributionrequired && $media->attributionrequired != 'false')
                <li>Attribution Required</li>
            @endif
            @if ($media->restrictions && $media->restrictions != 'false')
                <li><small>Restrictions:</small> {{ $media->restrictions }}</li>
            @endif
            </ul>
        </p>
        <p>
            <style>li { margin-bottom:6px; }</style>
            <em>Media information:</em>
            <ul>
                <li><small>Original datetime:</small> {{ $media->datetimeoriginal }}</li>
                <li><small>Upload datetime:</small> {{ $media->timestamp }}</li>
                <li><small>Uploader:</small> User:{{ $media->user }}</li>
                <li><small>SHA1:</small> <small>{{ $media->sha1 }}</small></li>
                <li><small>Refreshed:</small> {{ $media->updated_at }} UTC</li>
            </ul>
        </p>
        <p>
            <em>Technical Topics:</em>
            <br />
            <small>{{-- TODO: displayTopics --}}</small>
        </p>
    </div>
</div>
@endsection
```

#### `partials/thumbnail.blade.php`

```blade
@php
    // TODO: Replicate this logic
    // $thumb = $this->getThumbnail($data);
    $thumb = ['url' => $media->thumburl, 'width' => $media->thumbwidth, 'height' => $media->thumbheight];
@endphp
<div class="d-inline-block align-top text-right m-1 p-1"
     style="background-color:#eee;">
    <a style="line-height:90%;" class="nohover" href="{{ route('info', ['pageid' => $media->pageid]) }}">
        <img src="{{ $thumb['url'] }}" width="{{ $thumb['width'] }}" height="{{ $thumb['height'] }}" title="{{ $media->title }}" />
    <br >
    <div style="font-size:65%;">
        {{-- TODO: Replicate getMediaName --}}
        {{ Str::limit($media->title, 25) }}
        <br />
        &copy; {{-- TODO: Replicate getArtistName --}}
        {{ Str::limit($media->artist, 22) }}
        <br />
        {{-- TODO: Replicate getLicenseName --}}
        {{ Str::limit($media->licensename, 26) }}
    </div>
    </a>
    {{-- TODO: Replicate includeAdminMediaFunctions --}}
</div>
```

#### `browse.blade.php`

```blade
@extends('layouts.app')

@section('content')
@include('partials.menu')
<div class="row bg-white">
    <div class="col">
    <form class="form-inline">
        Browse Files -
        <select name="s">
            <option value="random" @if($sort == 'random') selected @endif>Random</option>
            <option value="pageid" @if($sort == 'pageid') selected @endif>ID</option>
            <option value="size" @if($sort == 'size') selected @endif>Size</option>
            <option value="title" @if($sort == 'title') selected @endif>File Name</option>
            <option value="mime" @if($sort == 'mime') selected @endif>Mime Type</option>
            <option value="width" @if($sort == 'width') selected @endif>Width</option>
            <option value="height" @if($sort == 'height') selected @endif>Height</option>
            <option value="datetimeoriginal" @if($sort == 'datetimeoriginal') selected @endif>Original Datetime</option>
            <option value="timestamp" @if($sort == 'timestamp') selected @endif>Upload Datetime</option>
            <option value="updated_at" @if($sort == 'updated_at') selected @endif>Last Refreshed</option>
            <option value="licenseuri" @if($sort == 'licenseuri') selected @endif>License URI</option>
            <option value="licensename" @if($sort == 'licensename') selected @endif>License Name</option>
            <option value="licenseshortname" @if($sort == 'licenseshortname') selected @endif>License Short Name</option>
            <option value="usageterms" @if($sort == 'usageterms') selected @endif>Usage Terms</option>
            <option value="attributionrequired" @if($sort == 'attributionrequired') selected @endif>Attribution Required</option>
            <option value="restrictions" @if($sort == 'restrictions') selected @endif>Restrictions</option>
            <option value="user" @if($sort == 'user') selected @endif>Uploading User</option>
            <option value="duration" @if($sort == 'duration') selected @endif>Duration</option>
            <option value="sha1" @if($sort == 'sha1') selected @endif>Sha1 Hash</option>
        </select>
        <select name="d">
            <option value="d" @if($direction == 'd') selected @endif>Descending</option>
            <option value="a" @if($direction == 'a') selected @endif>Ascending</option>
        </select>
        <input type="submit" value="Browse" />
    </form>
    {{ $medias->total() }}
    @if($sort == 'random')
        <a href="{{ route('browse', ['s' => 'random']) }}">Random</a>
    @endif
    Files
    {{ $medias->links() }}

    @auth
        <form action="{{ route('admin.media.multi-delete') }}" method="POST" name="media">
        @csrf
    @endauth

    <br clear="all" />
    @foreach ($medias as $media)
        {{-- TODO: display extra info --}}
        @include('partials.thumbnail', ['media' => $media])
    @endforeach
    <br clear="all" />

    {{ $medias->links() }}

    @auth
        {{-- TODO: includeAdminMediaListFunctions --}}
        </form>
    @endauth
    </div>
</div>
@endsection
```

## 4. Route Definitions

Next, define the routes for the application in the `laravel/routes/web.php` file.

Replace the contents of `laravel/routes/web.php` with the following code:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\UserMeController;
use App\Http\Controllers\RandomController;
use App\Http\Controllers\ScoresController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TopicsController;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\AdminAddController;
use App\Http\Controllers\Admin\AdminTopicController;
use App\Http\Controllers\Admin\AdminTopicMassController;
use App\Http\Controllers\Admin\AdminCurateController;
use App\Http\Controllers\Admin\AdminDatabaseController;
use App\Http\Controllers\Admin\AdminMediaController;
use App\Http\Controllers\Admin\AdminMediaBlockedController;
use App\Http\Controllers\Admin\AdminSiteController;
use App\Http\Controllers\Admin\AdminTagController;
use App\Http\Controllers\Admin\AdminUserController;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/b', [BrowseController::class, 'index'])->name('browse');
Route::get('/c/{topic?}/{subtopic?}/{subtopic2?}/{subtopic3?}', [TopicController::class, 'index'])->name('topic');
Route::get('/i/{info?}', [InfoController::class, 'index'])->name('info');
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::get('/logoff', [LogoutController::class, 'index'])->name('logoff');
Route::get('/logout', [LogoutController::class, 'index'])->name('logout');
Route::get('/me/{page?}', [UserMeController::class, 'index'])->name('user.me');
Route::get('/random', [RandomController::class, 'index'])->name('random');
Route::get('/scores/{user?}', [ScoresController::class, 'index'])->name('scores');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/tag', [TagController::class, 'index'])->name('tag');
Route::get('/topics', [TopicsController::class, 'index'])->name('topics');


Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminHomeController::class, 'index'])->name('home');
    Route::get('/add', [AdminAddController::class, 'index'])->name('add');
    Route::post('/add', [AdminAddController::class, 'store'])->name('add.store');
    Route::get('/topic', [AdminTopicController::class, 'index'])->name('topic');
    Route::get('/topic/mass', [AdminTopicMassController::class, 'index'])->name('topic.mass');
    Route::get('/curate', [AdminCurateController::class, 'index'])->name('curate');
    Route::post('/curate', [AdminCurateController::class, 'batchUpdate'])->name('curate.batch');
    Route::get('/database', [AdminDatabaseController::class, 'index'])->name('database');
    Route::post('/database', [AdminDatabaseController::class, 'action'])->name('database.action');
    Route::get('/database/download', [AdminDatabaseController::class, 'download'])->name('database.download');
    Route::get('/media', [AdminMediaController::class, 'index'])->name('media');
    Route::post('/media/add', [AdminMediaController::class, 'addMedia'])->name('media.add');
    Route::post('/media/multi-delete', [AdminMediaController::class, 'multiDelete'])->name('media.multi-delete');
    Route::post('/media/delete', [AdminMediaController::class, 'delete'])->name('media.delete');
    Route::post('/media/delete-in-topic', [AdminMediaController::class, 'deleteInTopic'])->name('media.delete-in-topic');
    Route::get('/media-blocked', [AdminMediaBlockedController::class, 'index'])->name('media.blocked');
    Route::get('/site', [AdminSiteController::class, 'index'])->name('site');
    Route::post('/site', [AdminSiteController::class, 'update'])->name('site.update');
    Route::get('/tag', [AdminTagController::class, 'index'])->name('tag');
    Route::get('/user', [AdminUserController::class, 'index'])->name('user');
});
```

## 5. Controller Logic

Next, create the controllers to handle the application logic.

### 5.1. Create Controller Files

Run the following Artisan commands from within the `laravel` directory to create the controller files:

```bash
php artisan make:controller HomeController
php artisan make:controller BrowseController
php artisan make:controller TopicController
php artisan make:controller InfoController
php artisan make:controller LoginController
php artisan make:controller LogoutController
php artisan make:controller UserMeController
php artisan make:controller RandomController
php artisan make:controller ScoresController
php artisan make:controller SearchController
php artisan make:controller SitemapController
php artisan make:controller TagController
php artisan make:controller TopicsController
php artisan make:controller Admin\\AdminHomeController
php artisan make:controller Admin\\AdminAddController
php artisan make:controller Admin\\AdminTopicController
php artisan make:controller Admin\\AdminTopicMassController
php artisan make:controller Admin\\AdminCurateController
php artisan make:controller Admin\\AdminDatabaseController
php artisan make:controller Admin\\AdminMediaController
php artisan make:controller Admin\\AdminMediaBlockedController
php artisan make:controller Admin\\AdminSiteController
php artisan make:controller Admin\\AdminTagController
php artisan make:controller Admin\\AdminUserController
```

### 5.2. Add Controller Code

Edit the newly created controller files in `laravel/app/Http/Controllers` and add the following code.

#### `HomeController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Site;
use App\Models\Media;

class HomeController extends Controller
{
    public function index()
    {
        $site = Site::firstOrCreate(['name' => 'Shared Media Tagger']);
        if (empty($site->about)) {
            $site->about = 'This website is temporarily offline.';
        }

        $randomMedia = Media::inRandomOrder()->limit(4)->get();

        $fileCount = Media::count();
        $topicCount = 0; // Topic model not yet created
        $voteCount = 0; // Tagging model not yet created

        return view('home', [
            'name' => $site->name,
            'about' => $site->about,
            'random' => $randomMedia,
            'countFiles' => number_format($fileCount),
            'countTopics' => number_format($topicCount),
            'countVotes' => number_format($voteCount),
            'title' => $site->name,
        ]);
    }
}
```

#### `BrowseController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Site;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    public function index(Request $request)
    {
        $pageLimit = 20;
        $sort = $request->input('s', 'random');
        $direction = $request->input('d', 'd') === 'a' ? 'asc' : 'desc';

        $query = Media::query();

        $siteInfo = Site::first();
        if ($siteInfo && $siteInfo->curation == 1) {
            $query->where('curated', 1);
        }

        $validSorts = [
            'pageid', 'size', 'title', 'mime', 'width', 'height',
            'datetimeoriginal', 'timestamp', 'updated_at', 'licenseuri',
            'licensename', 'licenseshortname', 'usageterms',
            'attributionrequired', 'restrictions', 'user', 'duration', 'sha1'
        ];

        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->inRandomOrder();
        }

        $medias = $query->paginate($pageLimit)->withQueryString();

        return view('browse', [
            'medias' => $medias,
            'sort' => $sort,
            'direction' => $request->input('d', 'd'),
            'title' => 'Browse Files',
        ]);
    }
}
```

#### `TopicController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Site;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request, $topic, $subtopic = null, $subtopic2 = null, $subtopic3 = null)
    {
        $pageLimit = 20;
        $topicName = $topic;
        if ($subtopic) {
            $topicName .= '/' . $subtopic;
        }
        if ($subtopic2) {
            $topicName .= '/' . $subtopic2;
        }
        if ($subtopic3) {
            $topicName .= '/' . $subtopic3;
        }

        $topicName = 'Category:' . $topicName;

        $topic = Topic::where('name', $topicName)->firstOrFail();

        $query = $topic->media();

        $siteInfo = Site::first();
        if ($siteInfo && $siteInfo->curation == 1) {
            $query->where('curated', 1);
        }

        $medias = $query->orderBy('pageid', 'asc')->paginate($pageLimit)->withQueryString();

        return view('topic', [
            'topic' => $topic,
            'medias' => $medias,
            'title' => $topic->name,
        ]);
    }
}
```

#### `InfoController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function index(Request $request, $pageid)
    {
        $media = Media::findOrFail($pageid);

        $media->imagedescriptionSafe = !empty($media->imagedescription)
            ? trim(strip_tags($media->imagedescription))
            : str_replace('File:', '', $media->title);

        $rows = 1;
        $rows += substr_count($media->imagedescriptionSafe, "\n");
        $rows += round(strlen($media->imagedescriptionSafe) / 70);
        $maxRows = 10;
        if ($rows > $maxRows) {
            $rows = $maxRows;
        }
        $media->imagedescriptionRows = $rows;

        $media->displayUrl = $media->thumburl;

        $height = $media->thumbheight;
        $width = $media->thumbwidth;

        $aspectRatio = 1;
        if ($width && $height) {
            $aspectRatio = $width / $height;
        }
        if ($aspectRatio < 1) { // Tall media
            $width = round($aspectRatio * 100);
        }
        if ($width > 100) {
            $width = 100;
        }
        $media->displayStyle = 'height:100%; width:' . $width . '%;';

        // Licensing
        $fix = [
            'Public domain' => 'Public Domain',
            'CC-BY-SA-3.0' => 'CC BY-SA 3.0'
        ];
        foreach ($fix as $bad => $good) {
            if ($media->usageterms == $bad) {
                $media->usageterms = $good;
            }
            if ($media->licensename == $bad) {
                $media->licensename = $good;
            }
            if ($media->licenseshortname == $bad) {
                $media->licenseshortname = $good;
            }
        }
        $lics = [$media->licensename, $media->licenseshortname, $media->usageterms];
        $media->licensing = array_unique($lics);

        return view('info', [
            'media' => $media,
            'title' => 'Info: ' . str_replace('File:', '', $media->title),
        ]);
    }
}
```

#### `LoginController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
}
```

#### `LogoutController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function index()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}
```

#### `UserMeController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Tagging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            // Or get the anonymous user
            // For now, let's assume we are dealing with logged in users
            return redirect()->route('login');
        }

        $pageLimit = 20;

        $query = Media::query()
            ->join('taggings', 'media.pageid', '=', 'taggings.media_pageid')
            ->where('taggings.user_id', $user->id)
            ->select('media.*', 'taggings.created_at as tagged_at')
            ->orderBy('tagged_at', 'desc');

        $medias = $query->paginate($pageLimit)->withQueryString();

        return view('user.me', [
            'medias' => $medias,
            'title' => 'My Taggings',
        ]);
    }
}
```

#### `RandomController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class RandomController extends Controller
{
    public function index()
    {
        $randomMedia = Media::inRandomOrder()->first();

        if ($randomMedia) {
            return redirect()->route('info', ['pageid' => $randomMedia->pageid]);
        }

        return redirect()->route('home');
    }
}
```

#### `ScoresController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Tagging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoresController extends Controller
{
    public function index(Request $request)
    {
        $pageLimit = 20;

        $query = Media::query()
            ->join('taggings', 'media.pageid', '=', 'taggings.media_pageid')
            ->join('tags', 'taggings.tag_id', '=', 'tags.id')
            ->select(
                'media.*',
                DB::raw('SUM(tags.score) as total_score'),
                DB::raw('COUNT(taggings.id) as votes'),
                DB::raw('SUM(tags.score) * 1.0 / COUNT(taggings.id) as score')
            )
            ->groupBy('media.pageid')
            ->orderBy('score', 'desc')
            ->orderBy('votes', 'desc');

        $scores = $query->paginate($pageLimit)->withQueryString();

        return view('scores', [
            'scores' => $scores,
            'title' => 'Scores',
        ]);
    }
}
```

#### `SearchController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $results = [];

        if (!empty($query)) {
            $results = Media::query()
                ->where('title', 'LIKE', "%{$query}%")
                ->orWhere('imagedescription', 'LIKE', "%{$query}%")
                ->orWhere('artist', 'LIKE', "%{$query}%")
                ->paginate(20)->withQueryString();
        }

        return view('search', [
            'query' => $query,
            'results' => $results,
            'title' => 'Search',
        ]);
    }
}
```

#### `SitemapController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $topics = Topic::query()
            ->join('topic_media', 'topics.id', '=', 'topic_media.topic_id')
            ->select('topics.name')
            ->distinct()
            ->get();

        $media = Media::query()->select('pageid')->get();

        $content = view('sitemap', [
            'topics' => $topics,
            'media' => $media,
            'time' => gmdate('Y-m-d'),
        ])->render();

        return Response::make($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
```

#### `TagController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\AnonymousUser;
use App\Models\Media;
use App\Models\Tag;
use App\Models\Tagging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $mediaId = $request->input('m');
        $tagId = $request->input('t');

        if (!$tagId || !is_numeric($tagId)) {
            return $this->redirect('Tag ID invalid');
        }

        if (!$mediaId || !is_numeric($mediaId)) {
            return $this->redirect('Media ID invalid');
        }

        $user = Auth::user();
        $anonymousUser = null;
        if (!$user) {
            $anonymousUser = AnonymousUser::firstOrCreate([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        if (!Tag::where('id', $tagId)->exists()) {
            return $this->redirect('Tag Not Found');
        }

        if (!Media::where('pageid', $mediaId)->exists()) {
            return $this->redirect('Media Not Found');
        }

        $userId = $user ? $user->id : null;
        $anonymousUserId = $anonymousUser ? $anonymousUser->id : null;

        $query = Tagging::query()
            ->where('media_pageid', $mediaId);

        if ($user) {
            $query->where('user_id', $userId);
        } else {
            $query->where('anonymous_user_id', $anonymousUserId);
        }

        $existingRating = $query->first();

        if ($existingRating) {
            if ($existingRating->tag_id == $tagId) {
                return $this->redirect('OK: user confirmed existing rating');
            }
            $existingRating->tag_id = $tagId;
            $existingRating->save();
            return $this->redirect('OK: user changed existing rating');
        }

        Tagging::create([
            'tag_id' => $tagId,
            'media_pageid' => $mediaId,
            'user_id' => $userId,
            'anonymous_user_id' => $anonymousUserId,
        ]);

        return $this->redirect('OK: user added rating');
    }

    private function redirect(string $message = '')
    {
        // For now, redirect to a random media
        $next = Media::inRandomOrder()->first();
        if ($next) {
            return redirect()->route('info', ['pageid' => $next->pageid]);
        }
        return redirect()->route('home');
    }
}
```

#### `TopicsController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicsController extends Controller
{
    public function index(Request $request)
    {
        $pageLimit = 1000;
        $search = $request->input('s', '');
        $hidden = $request->input('h', 0);

        $query = Topic::query()->where('local_files', '>', 0);

        if ($hidden) {
            $query->where('hidden', '>', 0);
        } else {
            $query->where('hidden', '<', 1);
        }

        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $query->orderBy('local_files', 'desc')->orderBy('name', 'asc');

        $topics = $query->paginate($pageLimit)->withQueryString();

        return view('topics', [
            'topics' => $topics,
            'search' => $search,
            'hidden' => $hidden,
            'title' => 'Topics',
        ]);
    }
}
```

#### `Admin/AdminHomeController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminHomeController extends Controller
{
    public function index()
    {
        return view('admin.home', [
            'title' => 'Admin Home',
        ]);
    }
}
```

#### `Admin/AdminAddController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
// use Attogram\SharedMedia\Api\Category as ApiTopic; // Placeholder
// use Attogram\SharedMedia\Api\Media as ApiMedia; // Placeholder

class AdminAddController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $type = $request->input('t', 'topics');
        $results = [];

        if (!empty($query)) {
            switch ($type) {
                case 'topics':
                    // $results = $this->searchTopics($query, 250); // Placeholder
                    break;
                case 'media':
                    // $results = $this->searchMedia($query, 20); // Placeholder
                    break;
            }
        }

        return view('admin.add', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'title' => 'Add Media to Collection',
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('ti')) {
            $this->addTopics($request->input('ti'));
        }

        if ($request->has('tm')) {
            // $this->addMediaFromTopic($request->input('tm')); // Placeholder
        }

        if ($request->has('ts')) {
            // $this->addSubtopicsFromTopic($request->input('ts')); // Placeholder
        }

        if ($request->has('m')) {
            // $this->addMedia($request->input('m')); // Placeholder
        }

        // TODO: Update topics local files count
        return redirect()->route('admin.add')->with('status', 'Items added!');
    }

    private function addTopics(array $pageids)
    {
        // $apiTopic = new ApiTopic(); // Placeholder
        // $apiTopic->setPageid(implode('|', $pageids));
        // $topics = $apiTopic->info();
        // foreach ($topics as $topic) {
        //     $this->saveTopic($topic);
        // }
    }

    private function saveTopic(array $topicData)
    {
        // Topic::updateOrCreate(
        //     ['name' => $topicData['title']],
        //     [
        //         'pageid' => $topicData['pageid'],
        //         'files' => $topicData['files'],
        //         'subcats' => $topicData['subcats'],
        //         'hidden' => $topicData['hidden'] ?? 0,
        //     ]
        // );
    }

    // ... other methods to be ported
}
```

#### `Admin/AdminTopicController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;

class AdminTopicController extends Controller
{
    public function index()
    {
        $topics = Topic::where('hidden', '!=', 1)
            ->orderBy('local_files', 'desc')
            ->orderBy('files', 'desc')
            ->orderBy('name', 'asc')
            ->limit(500)
            ->get();

        return view('admin.topic.index', [
            'topics' => $topics,
            'title' => 'Topic Admin',
        ]);
    }

    public function importMedia(Request $request)
    {
        $topicName = $request->input('i');
        // TODO: Implement getMediaFromTopic
        // $this->smt->database->getMediaFromTopic($topicName);
        // $this->smt->database->updateTopicsLocalFilesCount();
        return redirect()->route('admin.topic.index')->with('status', 'Media imported from topic.');
    }

    public function deleteTopic(Request $request)
    {
        $topicId = $request->input('d');
        Topic::destroy($topicId);
        // TODO: updateTopicsLocalFilesCount
        return redirect()->route('admin.topic.index')->with('status', 'Topic deleted.');
    }

    public function importSubtopics(Request $request)
    {
        $topicName = $request->input('sc');
        // TODO: Implement getSubcats
        // $this->smt->commons->getSubcats($topicName);
        // $this->smt->database->updateTopicsLocalFilesCount();
        return redirect()->route('admin.topic.index')->with('status', 'Subtopics imported.');
    }
}
```

#### `Admin/AdminTopicMassController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;

class AdminTopicMassController extends Controller
{
    public function index()
    {
        $topics = Topic::orderBy('updated_at', 'asc')
            ->orderBy('pageid', 'desc')
            ->limit(50)
            ->get();

        $refreshUrl = route('admin.add') . '?ti[]=' . implode('&ti[]=', $topics->pluck('pageid')->toArray());

        return view('admin.topic.mass', [
            'topics' => $topics,
            'refreshUrl' => $refreshUrl,
            'title' => 'Topic Mass Admin',
        ]);
    }
}
```

#### `Admin/AdminCurateController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class AdminCurateController extends Controller
{
    public function index(Request $request)
    {
        $pageLimit = $request->input('l', 20);
        if ($pageLimit > 1000) {
            $pageLimit = 1000;
        }
        if ($pageLimit < 1) {
            $pageLimit = 1;
        }

        $query = Media::where('curated', '!=', 1)->orderBy('updated_at', 'asc');

        if ($request->has('i')) {
            $medias = Media::where('pageid', $request->input('i'))->get();
        } else {
            $medias = $query->limit($pageLimit)->get();
        }

        $uncuratedCount = Media::where('curated', '!=', 1)->count();

        return view('admin.curate', [
            'medias' => $medias,
            'pageLimit' => $pageLimit,
            'uncuratedCount' => $uncuratedCount,
            'title' => 'Curation Admin',
        ]);
    }

    public function batchUpdate(Request $request)
    {
        if ($request->has('keep')) {
            Media::whereIn('pageid', $request->input('keep'))->update(['curated' => 1]);
        }

        if ($request->has('delete')) {
            Media::destroy($request->input('delete'));
            // TODO: updateTopicsLocalFilesCount
        }

        return redirect()->route('admin.curate.index')->with('status', 'Curation batch processed.');
    }
}
```

#### `Admin/AdminDatabaseController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminDatabaseController extends Controller
{
    public function index()
    {
        $databaseName = DB::connection()->getDatabaseName();
        $databaseWriteable = is_writable($databaseName);
        $databaseSize = file_exists($databaseName) ? number_format((float) filesize($databaseName)) : 'null';

        return view('admin.database.index', [
            'databaseName' => $databaseName,
            'databaseWriteable' => $databaseWriteable,
            'databaseSize' => $databaseSize,
            'title' => 'Database Admin',
        ]);
    }

    public function action(Request $request)
    {
        $action = $request->input('a');
        $result = '';

        switch ($action) {
            case 'create':
                Artisan::call('migrate');
                $result = 'Created Database Tables';
                break;
            case 'seed':
                Artisan::call('db:seed');
                $result = 'Demo Setup Seeded';
                break;
            case 'd':
                Artisan::call('migrate:fresh');
                $result = 'Dropped All Database Tables';
                break;
            case 'em':
                DB::table('media')->truncate();
                $result = 'Emptied Media Tables';
                break;
            case 'ec':
                DB::table('topics')->truncate();
                DB::table('topic_media')->truncate();
                $result = 'Emptied Topic Tables';
                break;
            case 'et':
                DB::table('taggings')->truncate();
                $result = 'Emptied Tagging Tables';
                break;
            case 'eu':
                DB::table('users')->truncate();
                DB::table('anonymous_users')->truncate();
                $result = 'Emptied User tables';
                break;
        }

        return redirect()->route('admin.database.index')->with('status', $result);
    }

    public function download()
    {
        $databaseName = DB::connection()->getDatabaseName();
        return response()->download($databaseName);
    }
}
```

#### `Admin/AdminMediaController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Topic;
use Illuminate\Http\Request;

class AdminMediaController extends Controller
{
    public function index()
    {
        return view('admin.media.index', [
            'title' => 'Media Admin',
        ]);
    }

    public function addMedia(Request $request)
    {
        // This seems to be handled by AdminAddController, but leaving a placeholder
        $mediaId = $request->input('am');
        // TODO: Add media logic
        return redirect()->route('admin.media.index')->with('status', 'Media added.');
    }

    public function multiDelete(Request $request)
    {
        $mediaIds = $request->input('media');
        if (is_array($mediaIds)) {
            Media::destroy($mediaIds);
            // TODO: updateTopicsLocalFilesCount
        }
        return redirect()->route('admin.media.index')->with('status', 'Media deleted.');
    }

    public function delete(Request $request)
    {
        $mediaId = $request->input('dm');
        Media::destroy($mediaId);
        // TODO: updateTopicsLocalFilesCount
        return redirect()->route('admin.media.index')->with('status', 'Media deleted.');
    }

    public function deleteInTopic(Request $request)
    {
        $topicName = $request->input('dc');
        $topic = Topic::where('name', $topicName)->first();
        if ($topic) {
            $mediaIds = $topic->media()->pluck('pageid');
            Media::destroy($mediaIds);
            // TODO: updateTopicsLocalFilesCount
        }
        return redirect()->route('admin.media.index')->with('status', 'Media in topic deleted.');
    }
}
```

#### `Admin/AdminMediaBlockedController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use Illuminate\Http\Request;

class AdminMediaBlockedController extends Controller
{
    public function index()
    {
        $blocks = Block::orderBy('pageid', 'asc')->limit(50)->get();

        return view('admin.media.blocked', [
            'blocks' => $blocks,
            'title' => 'Blocked Media Admin',
        ]);
    }
}
```

#### `Admin/AdminSiteController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class AdminSiteController extends Controller
{
    public function index()
    {
        $site = Site::firstOrCreate([]);

        return view('admin.site.index', [
            'site' => $site,
            'title' => 'Site Admin',
        ]);
    }

    public function update(Request $request)
    {
        $site = Site::firstOrCreate([]);
        $site->update($request->all());

        return redirect()->route('admin.site.index')->with('status', 'Site information updated.');
    }
}
```

#### `Admin/AdminTagController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminTagController extends Controller
{
    public function index()
    {
        // TODO: Implement admin tag logic
        return 'Admin Tag Page';
    }
}
```

#### `Admin/AdminUserController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        // TODO: Implement admin user logic
        return 'Admin User Page';
    }
}
```

#### `Media.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';
    protected $primaryKey = 'pageid';
    public $incrementing = false;

    protected $fillable = [
        'pageid',
        'curated',
        'title',
        'url',
        'descriptionurl',
        'descriptionshorturl',
        'imagedescription',
        'artist',
        'datetimeoriginal',
        'licenseuri',
        'licensename',
        'licenseshortname',
        'usageterms',
        'attributionrequired',
        'restrictions',
        'size',
        'width',
        'height',
        'sha1',
        'mime',
        'thumburl',
        'thumbwidth',
        'thumbheight',
        'thumbmime',
        'user',
        'userid',
        'duration',
        'timestamp',
    ];

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'topic_media', 'media_pageid', 'topic_id');
    }
}
```

#### `Site.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $table = 'site';

    protected $fillable = [
        'name',
        'about',
        'header',
        'footer',
        'curation',
    ];
}
```

#### `Tag.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'score',
        'name',
        'display_name',
    ];

    public function anonymousUsers(): BelongsToMany
    {
        return $this->belongsToMany(AnonymousUser::class, 'taggings');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'taggings', 'tag_id', 'media_pageid');
    }
}
```

### 3.3. Modify User Model

Edit the existing `User` model in `laravel/app/Models/User.php` to add the relationship with the `Tag` model.

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'taggings');
    }
}
```
