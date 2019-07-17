<?php
/**
 * Function: 注释解析器
 * Abo 2019/7/17 20:19
 * Email: abo2013@foxmail.com
 */

namespace Abo\Phpdoc\Logic;


class PaseComentLogic
{
    private $paseRet = [];
    private $data, $fileName;

    public function __construct( $data, $fileName )
    {
        $this->data = $data;
        $this->fileName = $fileName;
    }

    public function parse( $data, $fileName )
    {
        $fileName = basename( $fileName,'.php' );
        $return = array();

        list( $requestName, $requestUrl ) = $this->paseClassNameOrUrl();
        if( '[null]' == $requestName &&  '[null]' == $requestUrl ) { // 无备注 @method || @url 则放弃解析
            return false;
        }

        $this->paseFunctionName();
        $this->paseMethodName();
        $this->paseParam();


        preg_match_all('/\s+\*\s+@return\s+(.*?)\s+(.*?)\s+(.*?)\s/', $data, $matches);
        $return['return'] = array();
        if(empty($matches[1])){
            $return['return'] = array();
        }else{
            for($i=0;$i<count($matches[1]);$i++){
                $type = !empty($matches[1][$i]) ? $matches[1][$i] : '[null]';
                $var = !empty($matches[2][$i]) ? $matches[2][$i] : '[null]';
                $about = !empty($matches[3][$i]) ? $matches[3][$i] : '[null]';
                if(strpos($about,'*/') !== false){
                    $about = $var;
                    $var = '';
                }


                if($var!='*/' and $var!=''){
                    // echo "<script>console.log('{$fileName}-{$return['funcName']}-{$var}')</script>";
                    $return['return'][] = array(
                        'type' => $type,
                        'var' => $var,
                        'about' => $about,
                    );
                }

            }
        }

        return $return;

    }

    /**
     * 解析 代码方法名
     * @example public function index => index
     * @return string
     */
    protected function paseFunctionName()
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
    protected function paseMethodName()
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
    protected function paseClassNameOrUrl()
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
     * 解析 参数
     * @example: @param string img_title 头图   => img_title string 头图
     * @return array
     */
    protected function paseParam()
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

        return $this->paseRet['param'];
    }
}