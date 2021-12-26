<?php

class Qoo10API
{
    private $BASE_DOMAIN;
    private $API_KEY;
    private $USER_ID;
    private $USER_PWD;
    private $HanbaiAPIKey;

    /**
     * コンストラクタ
     * 
     * iniファイル読み込み
     * 販売認証キー取得
     */
    function __construct(){
        $ini = parse_ini_file("Qoo10API.ini");
        $this->BASE_DOMAIN = $ini["BASE_DOMAIN"];
        $this->API_KEY = $ini["API_KEY"];
        $this->USER_ID = $ini["USER_ID"];
        $this->USER_PWD = $ini["USER_PWD"];
        $this->HanbaiAPIKey = $this->CreateCertificationKey();
        if(!$this->HanbaiAPIKey){
            $this->HanbaiAPIKey = $this->CreateCertificationKey();
        }
    }

    /**
     * 確認用
     */
    public function var_dump() {
        var_dump($this);
    }

    /**
     * API実行用
     */
    private function APIExec($param){
        $queryString = http_build_query($param);
        $curl = curl_init();
        $auth_url = "https://".$this->BASE_DOMAIN."/GMKT.INC.Front.QAPIService/ebayjapan.qapi";
        $auth_url .= "?v=1.0";
        $auth_url .= "&".$queryString;
        curl_setopt($curl, CURLOPT_URL, $auth_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,TRUE);
    }

    /**
     * 販売認証キー発行
     */
    private function CreateCertificationKey(){
        $data = array();
        $data["returnType"] = "json";
        $data["method"] = "CertificationAPI.CreateCertificationKey";
        $data["key"] = $this->API_KEY;
        $data["user_id"] = $this->USER_ID;
        $data["pwd"] = $this->USER_PWD;
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return $ret["ResultObject"];
        } else {
            var_dump($ret);
        }
        return "";
    }

    /**
     * 商品情報照会
     */
    public function GetItemDetailInfo($SellerCode){
        $data = array();
        $data["returnType"] = "json";
        $data["method"] = "ItemsLookup.GetItemDetailInfo";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          if(count($ret["ResultObject"]) == 1){
            return $ret["ResultObject"][0];
          }
        }
        return "";
    }

    /**
     * 販売商品の組合せ型オプション情報照会
     */
    public function GetGoodsInventoryInfo($SellerCode){
        $data = array();
        $data["returnType"] = "json";
        $data["method"] = "ItemsLookup.GetGoodsInventoryInfo";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return $ret["ResultObject"];
        }
        return "";
    }

    /**
     * オプション在庫を削除
     */
    public function EditGoodsInventory($SellerCode){
        $data = array();
        $data["returnType"] = "json";
        $data["method"] = "ItemsOptions.EditGoodsInventory";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;
        $data["InventoryInfo"] = "";
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return true;
        }
        return false;
    }

    /**
     * オプション在庫を追加
     */
    public function InsertInventoryDataUnit($SellerCode, $Name1, $Value1, $Name2, $Value2, $Price, $Qty){
        $data = array();
        $data["returnType"] = "json";
        $data["method"] = "ItemsOptions.InsertInventoryDataUnit";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;
        $data["OptionName"] = $Name1 . "||*" . $Name2;
        $data["OptionValue"] = $Value1 . "||*" . $Value2;
        $data["OptionCode"] = "";
        $data["Price"] = $Price;
        $data["Qty"] = $Qty;
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return true;
        }
        return false;
    }



}

if (realpath($_SERVER["SCRIPT_FILENAME"]) == realpath(__FILE__)){
  $test = new Qoo10API();
  $test->var_dump();

  $options = $test->GetGoodsInventoryInfo("10000001");
  var_dump($options);
  
  $ret = $test->EditGoodsInventory("10000001");
  var_dump($ret);

  $options = $test->GetGoodsInventoryInfo("10000001");
  var_dump($options);

  $test->InsertInventoryDataUnit("10000001","サイズ", "70cm", "カラー", "グレー", 0, 7);
  $test->InsertInventoryDataUnit("10000001","サイズ", "70cm", "カラー", "レッド", 0, 4);
  $test->InsertInventoryDataUnit("10000001","サイズ", "80cm", "カラー", "グレー", 0, 2);
  $test->InsertInventoryDataUnit("10000001","サイズ", "80cm", "カラー", "レッド", 0, 0);

  $options = $test->GetGoodsInventoryInfo("10000001");
  var_dump($options);


}
