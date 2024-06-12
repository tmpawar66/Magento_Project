<?php

namespace VE\StockSheet\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
/**
 * Define all global functions to get throughout
 */
class Data extends AbstractHelper
{
    /**
     * @param Context $context
     */
    protected static $sizes = [
      '8xl',
      '7xl',
      '6xl',
      '5xl',
      '4xl',  
      'xxxxl',
      '3xl',
      'xxxl',
      '2xl',
      'xxl',
      'xl',
      'l',
      'm',
      's',
      'xs',
      'xxs',
      'xxxs'
      ];

    public function __construct(
        Context $context,
        \VE\StockSheet\Logger\Logger $logger,
        QuoteFactory $quoteFactory,
        OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
      $this->logger = $logger;
      $this->quoteFactory = $quoteFactory;
      $this->productRepository = $productRepository;
      $this->orderFactory = $orderFactory;
      parent::__construct($context);
    }

    public static function classify($value=null)
    {

        $value = mb_strtolower(trim($value));

        // handle ranges
        if (mb_strpos($value, '-')) {
        $values = explode('-', $value);
        $part1 = self::classify($values[0]);
        if ($part1) {
            $part2 = self::classify($values[1]);
            return $part1 - ($part2 / 100);
        }
        return false;
        }

        // handle clothing sizes
        if ($clothingSizeIndex = array_search($value, Data::$sizes)) {
        return $clothingSizeIndex * -1;
        }

        return false;
    }

    public static function compare($a, $b)
    {
        $classA = self::classify($a);
        $classB = self::classify($b);

        if ($classA && $classB) {
        if ($classA == $classB) return 0;
        return $classA > $classB ? 1 : -1;
        }

        return strnatcmp($a, $b);
    }

  public static function sort(array $arr)
  {
    usort($arr, 'self::compare');
    return $arr;
  }

  public function getAllData($id,$type)
  {
    $stdhdr1=
    [
     1=>["3XS","XXS","2XS","XS","S","M","L","XL"],
     2=>["2XL","3XL","4XL","5XL","6XL","7XL","8XL",""],
     3=>[2,4,6,8,10,12,14,16],
     4=>[18,20,22,24,26,28,30,32],
     5=>[34,36,38,40,42,44,46,48],
     6=>[00,"00-03","03-06","06-12","12-18","18-24","4-8","9-13"],
     7=>["ZERO","OS","N/A","XX/S","X/S","S/S","S/M","M/L"],
     8=>["L/XL","6/7XL","8/9XL","10/11XL","87S","92S","97S","102S"],
     9=>["107S","112S","117S","122S","127S","132S","67R","72R"],
     10=>["77R","82R","87R","92R","97R","102R","107R","112R"],
     11=>["117R","122R","127R","2T","3T","4T","5T","6T"]
    ];
    if($type === 'quote') {
      $allItems = $this->quoteFactory->create()->load($id);
    } else {
      $orderModel = $this->orderFactory->create();
      $allItems = $orderModel->loadByIncrementId($id);      
    }    
    $total_qty = 0;
    foreach($allItems->getAllVisibleItems()  as $_item) {
        $product = $this->productRepository->get($_item->getSku());
        $color = $product->getResource()->getAttribute('color')->getFrontend()->getValue($product);
        $size = $product->getResource()->getAttribute('size')->getFrontend()->getValue($product);
        $gender = $product->getResource()->getAttribute('gender')->getFrontend()->getValue($product);
        $allSize[] = $size;
        if($type === 'quote') {
          $qty = $_item->getQty();
        } else {
          $qty = $_item->getQtyOrdered();
        }
        $data[] = ['discription' => $_item->getName(),'gender'=>$gender, 'color' => $color, 'size' => $size, 'qty' => $qty]; 
        $total_qty = $total_qty + $qty; 
    }
    $newSizes =  $this->sort(array_unique($allSize));
    array_push($newSizes,'Total');
    $header = array_merge(['Description','Sex','Color'],$newSizes);
    foreach($newSizes as $rowdt){
      if($rowdt!="description" && $rowdt!="color" && $rowdt!="gender"){
        $rowdata[$rowdt]="";
      }
    }
    foreach($data as $key=>$rowdt) {
      $resultdata[$rowdt["discription"]][$rowdt["gender"]][$rowdt["color"]][$rowdt["size"]] = $rowdt["qty"];
    }
    $hdrgpdata=[];
    $arr_first=['Description','Sex','Color'];
    $arr_last=["Total"];
    foreach($resultdata as $dkey=>$desdata) {
      foreach($desdata as $gkey=>$genderdata) {
        foreach ($genderdata as $ckey => $colordata) {
          //header finder code
          $hcode=array_key_first($colordata);
          $cur_header_key=0;
           if(count($hdrgpdata)==0){
            foreach($stdhdr1 as $key=>$data){
             if(in_array($hcode,$data)){
             
              $hdrgpdata[$key]=array_merge($arr_first,$data,$arr_last);
              $hdrgpdata["data".$key]=[];
              $cur_header_key=$key;
             }
            }
            
           }
           else{
            $flag=0;
            foreach($hdrgpdata as $key=>$data){
              if(in_array($hcode,$data)){
               $cur_header_key=$key;
               $flag=1;
              }
             }
             if($flag==0){
              foreach($stdhdr1 as $key=>$data){
                if(in_array($hcode,$data)){
                  $hdrgpdata[$key]=array_merge($arr_first,$data,$arr_last);
                  $hdrgpdata["data".$key]=[];
                 $cur_header_key=$key;
                }
               }
             }
           }
           $hdr=[];
           foreach($hdrgpdata[$cur_header_key] as $d){
            $hdr[$d]="";
           }
           $resultdata[$dkey][$gkey][$ckey]=$hdr;
           $resultdata[$dkey][$gkey][$ckey]['Total']=array_sum($colordata);
           foreach($colordata as $qkey=>$qdata){
             $resultdata[$dkey][$gkey][$ckey][$qkey]=$qdata;
             $hdr[$qkey]=$qdata;
           }
          $hdr["Description"]=$dkey;
          $hdr["Sex"]=$gkey;
          $hdr["Color"]=$ckey;
          $hdr["Total"]=array_sum($colordata);
          $newArr = array_values($hdr);
          
          array_push($hdrgpdata["data".$cur_header_key],$newArr); 
                         
        }
      }
    }

    // echo "<pre/>";
    // print_r($hdrgpdata);
    // exit;

    // foreach ($finalData['resultdata'] as $keyR => $valueR) {
    //   $test['description'] = $keyR;
    //   foreach ($valueR as $keyG => $valueG) {
    //     $test['sex'] = $keyG;
    //     foreach ($valueG as $keyC => $valueC) {
    //       $test['color'] = $keyC;
    //       foreach ($valueC as $key => $value) {
    //         $test[$key] = $value;
    //       }
    //       $newArr = array_values($test);
    //       echo" <tr>";
    //       for ($i=0; $i < count($newArr); $i++) {
    //         echo "<td>".$newArr[$i]."</td>";
    //       }
    //       echo "</tr>";
    //     }
    //   }
    // }
    // return $finalArray;
    //old data
    

    
    $finalArray = ['resultdata'=>$hdrgpdata,'total'=>$total_qty];
return $finalArray;
    // echo "<pre/>";
    // print_r($finalArray);
    // exit;
  }
}