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
        $auth_url .= "?".$queryString;
        //var_dump($auth_url);
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
        $data["v"] = "1.0";
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
        $data["v"] = "1.1";
        $data["returnType"] = "json";
        $data["method"] = "ItemsLookup.GetItemDetailInfo";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
            //var_dump($ret);
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
        $data["v"] = "1.0";
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
        $data["v"] = "1.0";
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
        $data["v"] = "1.0";
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

    /**
     * オプション在庫を更新
     */
    public function UpdateInventoryDataUnit($SellerCode, $Name1, $Value1, $Name2, $Value2, $Price, $Qty){
        $data = array();
        $data["v"] = "1.0";
        $data["returnType"] = "json";
        $data["method"] = "ItemsOptions.UpdateInventoryDataUnit";
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
        } else {
            var_dump($ret);
        }
        return false;
    }

    /**
     * 出荷状態情報照会
     * ShippingStat:配送状態。（1：出荷待ち、2：出荷済み、3：発注確認、4：配送中、5：配送完了）
     * search_condition:日付の種類。（1：注文日、2：決済完了日、3：配送日、4：配送完了日）
     */
    public function GetShippingInfo_v2($ShippingStat,$search_condition){
        $data = array();
        $data["v"] = "1.0";
        $data["returnType"] = "json";
        $data["method"] = "ShippingBasic.GetShippingInfo_v2";
        $data["key"] = $this->HanbaiAPIKey;
        $data["ShippingStat"] = $ShippingStat;
        $data["search_Sdate"] = date("YmdHis", strtotime("-89 day"));
        $data["search_Edate"] = date('YmdHis');
        $data["search_condition"] = $search_condition;
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return $ret;
        }
        return false;
    }

    /**
     * 注文シングル件配送/クレーム情報照会
     */
    public function GetShippingAndClaimInfoByOrderNo_V2($OrderNo){
        $data = array();
        $data["v"] = "1.0";
        $data["returnType"] = "json";
        $data["method"] = "ShippingBasic.GetShippingAndClaimInfoByOrderNo_V2";
        $data["key"] = $this->HanbaiAPIKey;
        $data["OrderNo"] = $OrderNo;
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return $ret;
        }
        return false;
    }

    /**
     * 
     * 商品価格の更新
     */
    public function SetGoodsPriceQty($SellerCode, $Price){
        
        $data = array();
        $data["v"] = "1.0";
        $data["returnType"] = "json";
        $data["method"] = "ItemsOrder.SetGoodsPriceQty";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;
        $data["Price"] = $Price;
        $data["Qty"] = 9999;
        $data["ExpireDate"] = "2050-12-31";
        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return true;
        } else {
            var_dump($ret);
        }
        return false;
    }

    /**
     * 商品詳細更新
     */
    public function EditGoodsContents($SellerCode, $Contents){

        $curl = curl_init();
        $auth_url = "https://".$this->BASE_DOMAIN."/GMKT.INC.Front.QAPIService/ebayjapan.qapi/ItemsContents.EditGoodsContents";

        $headers = array(
            "QAPIVersion: 1.0",
            "GiosisCertificationKey: ".$this->HanbaiAPIKey,
        );
        $data = array();
        $data["v"] = "1.0";
        $data["returnType"] = "json";
        $data["method"] = "ItemsContents.EditGoodsContents";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;
        $data["Contents"] = $Contents;
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $auth_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $ret =  json_decode($response,TRUE);
        
        if($ret["ResultCode"] == 0){
          return true;
        } else {
            var_dump($ret);
        }
        return false;
    }
    

    /**
     * 商品のメイン画像変更
     */
    public function EditGoodsImage($SellerCode, $StandardImage){
        // /GMKT.INC.Front.QAPIService/ebayjapan.qapi/ItemsContents.EditGoodsImage
        $curl = curl_init();
        $auth_url = "https://".$this->BASE_DOMAIN."/GMKT.INC.Front.QAPIService/ebayjapan.qapi/ItemsContents.EditGoodsImage";

        $headers = array(
            "QAPIVersion: 1.1",
            "GiosisCertificationKey: ".$this->HanbaiAPIKey,
        );
        $data = array();
        $data["v"] = "1.0";
        $data["returnType"] = "json";
        $data["method"] = "ItemsContents.EditGoodsImage";
        $data["key"] = $this->HanbaiAPIKey;
        $item = $this->GetItemDetailInfo($SellerCode);
        $data["ItemCode"] = $item["ItemCode"];
        $data["SellerCode"] = $SellerCode;
        $data["StandardImage"] = $StandardImage;
        $data["VideoURL"] = "";
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $auth_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $ret =  json_decode($response,TRUE);

        if($ret["ResultCode"] == 0){
        return true;
        } else {
            var_dump($ret);
        }
        return false;

    }
    
    /**
     * 商品画像（メイン以外）変更
     */
    public function EditGoodsMultiImage($SellerCode, $urls){
        $curl = curl_init();
        $auth_url = "https://".$this->BASE_DOMAIN."/GMKT.INC.Front.QAPIService/ebayjapan.qapi/ItemsContents.EditGoodsMultiImage";

        $headers = array(
            "QAPIVersion: 1.1",
            "GiosisCertificationKey: ".$this->HanbaiAPIKey,
        );
        $data = array();
        $data["v"] = "1.0";
        $data["returnType"] = "json";
        $data["method"] = "ItemsContents.EditGoodsMultiImage";
        $data["key"] = $this->HanbaiAPIKey;
        $data["SellerCode"] = $SellerCode;

        $idx =1;
        foreach($urls as $img){
            if($idx > 11){
              break;
            }
            $data["EnlargedImage".$idx] = $img;
            $idx = $idx + 1;
        }

        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $auth_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $ret =  json_decode($response,TRUE);

        if($ret["ResultCode"] == 0){
        return true;
        } else {
            var_dump($ret);
        }
        return false;

    }
/*
Host: api.qoo10.jp
POST 
Content-Length: length
Content-Type: application/x-www-form-urlencoded
QAPIVersion: 1.0
GiosisCertificationKey: [Seller Authorization Key]
 
returnType: [text/xml] or [application/json] (Optional)
ItemCode:String
SellerCode:String
EnlargedImage1:String
EnlargedImage2:String
EnlargedImage3:String
EnlargedImage4:String
EnlargedImage5:String
EnlargedImage6:String
EnlargedImage7:String
EnlargedImage8:String
EnlargedImage9:String
EnlargedImage10:String
EnlargedImage11:String
*/

    /**
     * ItemsBasic.UpdateGoodsが動かない。
     */
    public function UpdateGoods($SellerCode, $ItemTitle){
        $item = $this->GetItemDetailInfo($SellerCode);
        var_dump($item);
        $data = array();
        $data["v"] = "1.1";
        $data["returnType"] = "json";
        $data["method"] = "ItemsBasic.UpdateGoods";
        $data["key"] = $this->HanbaiAPIKey;
        $data["ItemCode"] = $item["ItemCode"];
        $data["SecondSubCat"] = $item["SecondSubCatCd"];
        $data["ItemTitle"] = "TEST";//$item["ItemTitle"];
        $data["ProductionPlaceType"] = "1";//$item["ProductionPlaceType"];
        $data["AdultYN"] = $item["AdultYN"];
//        var_dump($data);
        /*
        $data["Drugtype"] = $item["Drugtype"];
        $data["ItemTitle"] = $ItemTitle;
        $data["PromotionName"] = $item["PromotionName"];
        $data["IndustrialCodeType"] = $item["IndustrialCodeType"];
        $data["IndustrialCode"] = $item["IndustrialCode"];
        $data["BrandNo"] = $item["BrandNo"];
        //$data["ManufactureDate"] = $item["BrandNo"];
        $data["ModelNm"] = $item["ModelNm"];
        $data["Material"] = $item["Material"];
        $data["ProductionPlace"] = $item["ProductionPlace"];
        $data["RetailPrice"] = $item["RetailPrice"];
        $data["ContactInfo"] = $item["ContactInfo"];
        $data["ShippingNo"] = $item["ShippingNo"];
        //$data["OptionShippingNo1"] = "OptionShippingNo1";
        //$data["OptionShippingNo2"] = "OptionShippingNo2";
        //$data["Weight"] = "Weight";
        $data["DesiredShippingDate"] = $item["DesiredShippingDate"];
        $data["AvailableDateType"] = $item["AvailableDateType"];
        $data["AvailableDateValue"] = $item["AvailableDateValue"];
        $data["Keyword"] = $item["Keyword"];
        */

/*

	String	9		登録された商品のQoo10商品コード
SecondSubCat	String	9		商品の種類に対応したQoo10カテゴリーコードです。
* QSM BulK-data ManagementでQoo10カテゴリーコード情報をダウンロードすることができます。 （ex.300000001）
Drugtype	String	2		医薬品カテゴリーを選択する際は必ず入力してください。 (1C : 第1類医薬品, 2C : 第2類医薬品, 3C : 第3類医薬品, D2 : 指定第2類医薬品, QD : 医薬部外品)
ItemTitle	String	Max 100		商品名
PromotionName	String	Max20		広告文
SellerCode	String	Max 100		販売者が管理している商品のコードです。商品登録後、その情報を利用して登録された商品を情報を照会したり、修正することができます。
IndustrialCodeType	String	1		商品識別コード(J: JAN、K: KAN、I: ISBN、U: UPC、E: EAN、H: HS)
IndustrialCode	String	13		製品の産業コードです。 （JAN、ISBN ...など）標準のコードを入力すると、価格比較サイトにて優先的に表示されることがあります。
BrandNo	String	Max 10		Qoo10に登録されたブランドのコードです。新規ブランド登録要請は、QSMを通じてリクエストすることができます。 （ex.36458）
ManufactureDate	String	7		製造日 YYY-MM-DD (ex: 2021-01-01)
ModelNm	String	Max 30		モデル名
Material	String	Max 500		商品の素材（ex：Polyester 50％、Synthetic 50％）
ProductionPlaceType	String	1		原産地タイプ（国内= 1、海外= 2、その他= 3）
ProductionPlace	String	Max 50		商品の原産情報（国または地域名）
RetailPrice	String	1~999999999		小売価格です。もし小売価格を知ることができない場合は、0に入力願います。
AdultYN	String	1		アダルトグッズの場合”Y”、アダルトグッズではない場合”N”
ContactInfo	String	Max100		アフターサービス情報
ShippingNo	String			Qoo10送料コード。QSMの送料管理メニューで、使用する送料のコードを確認してください。0を入力すると送料無料が設定されます。
OptionShippingNo1	String	0~2147483647		購入者が注文時に送料を選択できるように、商品に追加で設定する送料コード。QSMの送料管理メニューで、使用する送料のコードを確認してください。
OptionShippingNo2	String	0~2147483647		購入者が注文時に送料を選択できるように、商品に追加で設定する送料コード。QSMの送料管理メニューで、使用する送料のコードを確認してください。
Weight	String	kg		商品の重量（送料自動策定に役立ちます）
DesiredShippingDate	String	Max 2		希望出荷日。出荷のための最小の準備期間です。設定された準備期間以降の希望出荷日を購入者は注文時に選択することができます。 （無効にする= null、準備期間= 3〜20の間の数字）
AvailableDateType	String	1		"商品発送可能日タイプです。数字で入力してください。（0,1,2,3）
- 0：一般発送（3営業日内発送可能な商品）
- 1：商品準備日
- 2：発売日
- 3：当日発送
AvailableDateValue	String	10		"商品発送可能日タイプの詳細内容です。
- 時間を入力する場合、即日発送商品となります。 （即日発送時間を入力ex：14:30）
- 1〜3を入力する場合は、通常発送商品となります。（一発発送日を入力ex：1）
- 4〜14を入力する場合は、 商品準備日の設定の商品になります。（商品準備日を入力ex：5）
- 日の形式で入力する場合、発売予定日がされます。（発売日を入力ex：2013/09/26）
Keyword	String	Max 10 keywords, a keyword: Max 30		検索ワード 10個まで設定可能(ex: シャツ、デニムシャツ、デニムのシャツ)

*/

        $ret = $this->APIExec($data);
        if($ret["ResultCode"] == 0){
          return $ret;
        }
        return $ret;
    }
    
}

if (realpath($_SERVER["SCRIPT_FILENAME"]) == realpath(__FILE__)){
  $test = new Qoo10API();
  $test->var_dump();

  
  //$ret = $test->SetGoodsPriceQty("10000001", "3210");
  //var_dump($ret);
  //$ret = $test->GetItemDetailInfo("10000001");
  //var_dump($ret["ItemTitle"]);
  //$test->UpdateGoods("10000001", "子供服 カバーオール 男の子 女の子 長袖【裏起毛ニット風動物フェアアイル柄ベビー 長袖ロンパース");
  //$ret = $test->GetItemDetailInfo("10000001");
  //var_dump($ret["ItemTitle"]);


  /*
  $order = $test->GetShippingAndClaimInfoByOrderNo_V2(698016773);
  var_dump($order);
  */
  /*
  $ret = $test->GetShippingInfo_v2("","1");
  var_dump($ret);
  */

  /*
  $options = $test->GetGoodsInventoryInfo("10000001");
  var_dump($options);
  $test->InsertInventoryDataUnit("10000001","サイズ", "70cm", "カラー", "グレー", 0, 5);
  var_dump($options);
  $options = $test->GetGoodsInventoryInfo("10000001");
  var_dump($options);
  $test->InsertInventoryDataUnit("10000001","サイズ", "70cm", "カラー", "グレー", 0, 7);
  var_dump($options);
  */

  /*
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
*/

}
