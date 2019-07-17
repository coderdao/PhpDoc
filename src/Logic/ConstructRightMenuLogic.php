<?php
/**
 * Function: 构建右侧导航栏
 * Description:
 * Abo 2019/7/17 21:41
 * Email: abo2013@foxmail.com
 */

namespace Abo\Phpdoc\Logic;


class ConstructRightMenuLogic
{
    /**
     * 生成侧边栏
     * @param array $rightList 侧边列表数组
     * @return string html代码
     */
    public function construct( array $rightList )
    {
        $return = '';
        if ( !$rightList ) { return $return; }

        foreach( $rightList as $d => $file ) {
            $return .= '<blockquote class="layui-elem-quote layui-quote-nm right-item-title">'.$d.'</blockquote>
            <ul class="right-item">';
            foreach( $file as $one ) {
                $return .= '<li class=""><a href="#'.base64_encode($one['requestUrl']).'"><cite>'.$one['methodName'].'</cite><em>'.$one['requestUrl'].'</em></a></li>';
            }
            $return .= '</ul>';
        }

        return $return;
    }
}