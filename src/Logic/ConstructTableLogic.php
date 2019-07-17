<?php
/**
 * Function: 构建文档表单
 * Description:
 * Abo 2019/7/17 21:41
 * Email: abo2013@foxmail.com
 */

namespace Abo\Phpdoc\Logic;


class ConstructTableLogic
{
    private $data, $tableHtml; // 每个API的信息 由parse返回的

    public function __construct( array $paseData )
    {
        $this->data = $paseData;
    }

    public function construct()
    {
        $this->constructTitle();
    }

    private function constructTitle()
    {
        $this->tableHtml = '<div id="'.base64_encode( $this->data['requestUrl']).'" class="api-main">
        <div class="title">'. $this->data['methodName'].'</div>
        <div class="body">
            <table class="layui-table">
                <thead>
                    <tr>
                        <th> '. $this->data['requestName'].' </th>
                        <th rowspan="3"> '. $this->data['requestUrl'].' </th>
                    </tr>
                </thead>
            </table>
        </div>';
    }
}