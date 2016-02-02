<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$dateFrom = date("d.m.Y", time() - (24*60*60*60));
$dateTo = date("d.m.Y");
$quantity = 20;

$selectNews = ["ID", "NAME", "SHOW_COUNTER", "DATE_CREATE", "DETAIL_PAGE_URL", "IBLOCK_SECTION_ID"];
$filterNews = ["IBLOCK_ID"=>"1", "ACTIVE"=>"Y"];

$news = getAllNewsArray($selectNews, $filterNews);
$newsByDate = getNewsByDateFilter($news, $dateFrom, $dateTo);
$newsByCounterAndDate = sortNewsByCounterASC($newsByDate);
$newsByCounterAndDateDESC = array_reverse($newsByCounterAndDate);
showNewsHtml($newsByCounterAndDateDESC, $quantity);


function getNewsByDateFilter($newsArray, $dateFrom, $dateTo) {
    $dateFromTime = strtotime($dateFrom);
    $dateToTime = strtotime($dateTo);
    $newsFilterResult = [];
    foreach ($newsArray as $newsElement) {
        $newsElementDateCreateTime = strtotime($newsElement["DATE_CREATE"]);
        if($newsElementDateCreateTime>$dateFromTime && $newsElementDateCreateTime<$dateToTime) {
            array_push($newsFilterResult, $newsElement);
        }
    }
    return $newsFilterResult;
}

function sortNewsByCounterASC($newsArray) {
    for ($x = 0; $x < count($newsArray); $x++) {
        for ($y = 0; $y < count($newsArray); $y++) {
            if ($newsArray[$x]["SHOW_COUNTER"] < $newsArray[$y]["SHOW_COUNTER"]) {
                $hold = $newsArray[$x];
                $newsArray[$x] = $newsArray[$y];
                $newsArray[$y] = $hold;
            }
        }
    }
    return $newsArray;
}

function getAllNewsArray($select, $filter) {
    $newsArray = [];
    $allNews = CIBlockElement::GetList([], $filter, false, false, $select);
    while($newsElement = $allNews->GetNextElement()) {
        $newsElementFields = $newsElement->GetFields();
        array_push($newsArray, $newsElementFields);
    }
    return $newsArray;
}

function showNewsHtml($newsArray, $quantityNews){
    ?>
    <div class="zag_popular">САМОЕ ЧИТАЕМОЕ</div>
    <div id="latestnews">
        <div class="scroll-crutch scroll-crutch-popular">
            <?
            for ($x = 0; $x < $quantityNews; $x++) {
                ?>
                <div class="">
                    <div class="lastnewshead lastnewshead_popular"><?=changeFormatDate($newsArray[$x]['DATE_CREATE'])?> /
                        <a class="lastnewslink popular_link" href="<?=getNewsSectionURL($newsArray[$x]['IBLOCK_SECTION_ID'])?>">
                            <?=getNewsSectionName($newsArray[$x]['IBLOCK_SECTION_ID'])?></a>
                        <? if (isset($newsArray[$x]['SHOW_COUNTER']))echo " / Просмотров: " . $newsArray[$x]['SHOW_COUNTER'];?>
                        <? if (isset($newsArray[$x]['DISPLAY_PROPERTIES']['FORUM_MESSAGE_CNT']['VALUE']))
                            echo " / Комментариев: ".$newsArray[$x]['DISPLAY_PROPERTIES']['FORUM_MESSAGE_CNT']['VALUE']; ?>
                    </div>
                    <div class="lastnewsitem lastnewsitem_popular">
                        <a href="<?=$newsArray[$x]['DETAIL_PAGE_URL']?>"><?=$newsArray[$x]['NAME']?></a>
                    </div>
                </div>
                <?
            }
            ?>
        </div>
    </div>
    <?
}

function changeFormatDate($newsDate) {
    $resultMonth = [
        "01" => "января",
        "02" => "февраля",
        "03" => "марта",
        "04" => "апреля",
        "05" => "мая",
        "06" => "июня",
        "07" => "июля",
        "08" => "августа",
        "09" => "сентября",
        "10" => "октября",
        "11" => "ноября",
        "12" => "декабря",
    ];

    $day = date('j', strtotime($newsDate));
    $month = date('m', strtotime($newsDate));
    $year = date('Y', strtotime($newsDate));
    $dateResult = $day. " " .$resultMonth[$month]. " " . $year;

    if (date('d.m.Y', strtotime($newsDate)) == date('d.m.Y')) {
        $dateResult = 'Сегодня';
    }
    elseif (date('d.m.Y', strtotime($newsDate)) == date('d.m.Y', time() - (24*60*60))) {
        $dateResult = 'Вчера';
    }
    return $dateResult;
}

function getNewsSectionName($newsSectionId){
    $sectionName = 'Новости';
    $section_id = $newsSectionId;
    $sectionElement = CIBlockSection::GetByID($section_id);
    if($sectionField = $sectionElement->GetNext()){
        $sectionName = $sectionField['NAME'];
    }
    return $sectionName;
}

function getNewsSectionURL($newsSectionId){
    $sectionURL = '/news/';
    $section_id = $newsSectionId;
    $sectionElement = CIBlockSection::GetByID($section_id);
    if($sectionField = $sectionElement->GetNext()){
        $sectionURL = $sectionField['SECTION_PAGE_URL'].'/  ';
    }
    return $sectionURL;
}
