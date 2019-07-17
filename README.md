# PhpDoc

#### 介绍 ( Introduction )
```text
需有 @method 或 @url 才会产生文档,其他按 Idea 提示 填写注解即可

It takes @method or @url to be useful, Others follow the Idea prompts to fill in the notes.
According to the prompt of Idea, fill in the note, but you must have @method or @url to be useful
```
[PHP Note Reference](https://github.com/yinggaozhen/doc-demo/tree/master/php)


#### 使用说明 ( Usage )

1.安装扩展 ( Installing the extension )
```text
composer require abo/phpdoc
```

2.填写方法注解 ( Fill in method notes )
```text
 * 列表
 * @url /fasterapi/head_img
 * @method HeadImgController::index
 * @param string img_title 头图
 * @param string img_src 路径
 * @param string jump_url_intro 跳转介绍
 * @param string jump_url 跳转路径
 *
 * @return \Illuminate\Http\JsonResponse Json json数组
 * @throws ApiException Json 接口异常
 * @throws \Abo\Generalutil\V1\Exceptions\PageException Html 页面异常

```

3. 运行脚本 ( Run the script )
```text
    $Doc = new \Abo\Phpdoc\Doc( './app/Http/Controllers', './' );
    return $Doc->make( true );
```
