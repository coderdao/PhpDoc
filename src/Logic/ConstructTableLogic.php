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
    private $data, $tableHtml = ''; // 每个API的信息 由parse返回的

    /**
     * 设置文档数据
     * @param array $data 每个API的信息 由parse返回的
     * @return $this
     */
    public function data( array $paseData )
    {
        $this->tableHtml = '';
        $this->data = $paseData;
        return $this;
    }

    /**
     * 每个文档生成表格
     * @return string html代码
     */
    public function construct()
    {
        if ( !$this->data ) { return ''; }

        $this->constructTitle();

        $this->constructArray( 'param', '参数' );
        $this->constructArray( 'return', '返回' );
        $this->constructArray( 'throws', '异常' );

        return $this->tableHtml.'<hr></div>';
    }

    /**
     * 构建头部
     * @return string
     */
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

        return $this->tableHtml;
    }

    /**
     * 构建 数组内容: 请求参数/返回
     * @return string
     */
    private function constructArray( string $constructArrayKey, string $label )
    {
        if ( !$constructArrayKey ) { return $this->tableHtml; }

        // 请求参数
        $data = $this->data;
        if( !isset( $data[ $constructArrayKey ] ) || !$data[ $constructArrayKey ] ){ return $this->tableHtml; }

        $this->tableHtml .= '<div class="body">
                <table class="layui-table">
                    <thead>
                        <tr> 
                            <th>'.$label.'名称</th> 
                            <th>'.$label.'类型</th> 
                            <th>'.$label.'说明</th> 
                        </tr>
                    </thead>
                    <tbody>';

        foreach( $data[ $constructArrayKey ] as $param ){
            $this->tableHtml .= "<tr> 
                    <td>{$param['var']}</td> 
                    <td>{$param['type']}</td> 
                    <td>{$param['about']}</td>
                </tr>";
        }

        $this->tableHtml .= '</tbody>
                </table>
            </div>';

        return $this->tableHtml;
    }


}