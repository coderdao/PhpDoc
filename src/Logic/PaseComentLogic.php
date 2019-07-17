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

        $this->paseFunctionName();
        $this->paseMethodName();
        $this->paseClassNameOrUrl();

        if($return['requestName']=='[null]' and $return['requestUrl']=='[null]'){
            return false;
        }

        if($this->controllerChange == true){
            $return['requestUrl'] = str_replace('{controller}',$this->humpToLine($fileName,$this->controllerTimes),$return['requestUrl']);
        }
        if($this->methodChange == true){
            $return['requestUrl'] = str_replace('{action}',$this->humpToLine($return['funcName'],$this->methodTimes),$return['requestUrl']);
        }
        $return['requestUrl'] = str_replace('{url}',$this->url,$return['requestUrl']);

        preg_match_all('/\s+\*\s+@param\s+(.*?)\s+(.*?)\s+(.*?)\s/', $data, $matches);
        if(empty($matches[1])){
            $return['param'] = array();
        }else{
            for($i=0;$i<count($matches[1]);$i++){
                $type = !empty($matches[1][$i]) ? $matches[1][$i] : '[null]';
                $var = !empty($matches[2][$i]) ? $matches[2][$i] : '[null]';
                $about = !empty($matches[3][$i]) ? $matches[3][$i] : '[null]';
                $return['param'][] = array(
                    'type' => $type,
                    'var' => $var,
                    'about' => $about,
                );
            }
        }
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
     * 代码方法名 解析
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
     * 注释方法名 解析
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
     * 命名路径 & url 解析
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
}