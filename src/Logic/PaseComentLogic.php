<?php
/**
 * Function: 注释解析器
 * Description: 解析每一条可以生成API文档的注释成数组
 * Abo 2019/7/17 20:19
 * Email: abo2013@foxmail.com
 */

namespace Abo\Phpdoc\Logic;


class PaseComentLogic
{
    private $paseRet = [];
    private $data, $fileName;

    /**
     * @param string $data 注释文本 catchEvery返回的每个元素
     * @param string $fileName 文件名
     */
    public function __construct( string $data, string $fileName )
    {
        $this->data = $data;
        $this->fileName = $fileName;
    }

    public function parse()
    {
        list( $requestName, $requestUrl ) = $this->paseClassNameOrUrl();
        if( '[null]' == $requestName &&  '[null]' == $requestUrl ) { // 无备注 @method || @url 则放弃解析
            return false;
        }

        $this->paseFunctionName();
        $this->paseMethodName();

        $this->paseParam();     // 请求参数
        $this->paseReturn();    // 返回结果
        $this->paseThrows();    // 异常

        return $this->paseRet;
    }

    /**
     * 解析 代码方法名
     * @example public function index => index
     * @return string
     */
    private function paseFunctionName()
    {
        preg_match_all(
            '/(public|private|protected)?\s*function\s+(?<funcName>.*?)\(/',
            $this->data,
            $matches
        );

        return $this->paseRet['funcName'] =
            !empty( $matches[ 'funcName' ][0] ) ? $matches[ 'funcName' ][0] : '[null]';
    }

    /**
     * 解析 注释方法名
     * @example: * 列表 => 列表
     * @return string
     */
    private function paseMethodName()
    {
        preg_match_all(
            '/\/\*\*\s+\*\s+(?<methodName>.*?)\s+\*/s',
            $this->data,
            $matches
        );

        return $this->paseRet['methodName'] =
            !empty($matches['methodName'][0]) ? $matches['methodName'][0] : '[null]';
    }

    /**
     * 解析 命名路径 & url
     * @example: @method HeadImgController::index   => HeadImgController::index
     * @example: @url /fasterapi/head_img           => /fasterapi/head_img
     * @return array
     */
    private function paseClassNameOrUrl()
    {
        // 类名路径
        preg_match_all(
            '/\s+\*\s+\@method\s+(?<requestName>.*)?.*/',
            $this->data,
            $matches
        );
        $this->paseRet['requestName'] =
            !empty($matches['requestName'][0]) ? $matches['requestName'][0] : '[null]';

        // 请求url
        preg_match_all(
            '/\s+\*\s+\@url\s+(?<requestUrl>.*)?.*/',
            $this->data,
            $matches
        );
        $this->paseRet['requestUrl'] =
            !empty($matches['requestUrl'][0]) ? $matches['requestUrl'][0] : '[null]';

        return [ $this->paseRet['requestName'], $this->paseRet['requestUrl'] ];
    }

    /**
     * 解析 请求参数
     * @example: @param string img_title 头图   => img_title string 头图
     * @return array
     */
    private function paseParam()
    {
        preg_match_all(
            '/\s+\*\s+@param\s+(.*?)\s+(.*?)\s+(.*?)\s/',
            $this->data,
            $matches
        );

        if( empty( $matches[ 1 ] ) ) {
            $this->paseRet[ 'param' ] = array();
        } else {
            $count4Matches = count( $matches[ 1 ] );

            for ( $i=0; $i < $count4Matches; $i++ ) {
                $type = !empty( $matches[1][$i] ) ? $matches[1][$i] : '[null]';
                $var = !empty( $matches[2][$i] ) ? $matches[2][$i] : '[null]';
                $about = !empty( $matches[3][$i] ) ? $matches[3][$i] : '[null]';

                $this->paseRet[ 'param' ][] = [
                    'type' => $type,
                    'var' => $var,
                    'about' => $about,
                ];
            }
        }

        return true;
    }

    /**
     * 解析 返回
     * @example: @return JsonResponse Json json数组  => Json JsonResponse json数组
     * @return array
     */
    private function paseReturn()
    {
        preg_match_all(
            '/\s+\*\s+@return\s+(.*?)\s+(.*?)\s+(.*?)\s/',
            $this->data,
            $matches
        );

        if ( empty( $matches[1] ) ) {
            $this->paseRet[ 'return' ] = [];
        } else {
            $count4Matches = count( $matches[ 1 ] );

            for ( $i = 0; $i < $count4Matches; $i++ ) {
                $type = !empty( $matches[1][$i] ) ? $matches[1][$i] : '[null]';
                $var = !empty( $matches[2][$i] ) ? $matches[2][$i] : '[null]';
                $about = !empty( $matches[3][$i] ) ? $matches[3][$i] : '[null]';
                if ( false !== strpos( $about,'*/' ) ) {
                    $about = $var;
                    $var = '';
                }


                if ( $var != '*/' && $var != '' ) {
                    // echo "<script>console.log('{$fileName}-{$this->paseRet['funcName']}-{$var}')</script>";
                    $this->paseRet[ 'return' ][] = [
                        'type' => $type,
                        'var' => $var,
                        'about' => $about,
                    ];
                }

            }
        }

        return true;
    }

    /**
     * 解析 异常
     * @example: @return JsonResponse Json json数组  => Json JsonResponse json数组
     * @return array
     */
    private function paseThrows()
    {
        preg_match_all(
            '/\s+\*\s+@throws\s+(.*?)\s+(.*?)\s+(.*?)\s/',
            $this->data,
            $matches
        );

        if ( empty( $matches[1] ) ) {
            $this->paseRet[ 'throws' ] = [];
        } else {
            $count4Matches = count( $matches[ 1 ] );

            for ( $i = 0; $i < $count4Matches; $i++ ) {
                $type = !empty( $matches[1][$i] ) ? $matches[1][$i] : '[null]';
                $var = !empty( $matches[2][$i] ) ? $matches[2][$i] : '[null]';
                $about = !empty( $matches[3][$i] ) ? $matches[3][$i] : '[null]';
                if ( false !== strpos( $about,'*/' ) ) {
                    $about = $var;
                    $var = '';
                }


                if ( $var != '*/' && $var != '' ) {
                    $this->paseRet[ 'return' ][] = [
                        'type' => $type,
                        'var' => $var,
                        'about' => $about,
                    ];
                }

            }
        }

        return true;
    }
}