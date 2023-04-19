<?php 

use backend\assets\AppAsset; 
use yii\helpers\Html; 
use backend\models\Menu;
use common\models\Format;
AppAsset::register($this); 

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Title</title>
    <style>
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed,
        figure, figcaption, footer, header, hgroup,
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }
        /* HTML5 display-role reset for older browsers */
        article, aside, details, figcaption, figure,
        footer, header, hgroup, menu, nav, section {
            display: block;
        }
        body {
            line-height: 1;
        }
        ol, ul {
            list-style: none;
        }
        blockquote, q {
            quotes: none;
        }
        blockquote:before, blockquote:after,
        q:before, q:after {
            content: '';
            content: none;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>
    <!--time kefu-->
    <div style="display: flex;flex-direction: row;width: 100%;height: 60px;justify-content: flex-start;align-items: center;padding-left: 10px;border-bottom: 1px solid #ccc;">

        <div>客服时间:09:00--18:00</div>

    </div>


    <div style="display: flex;flex-direction: row;width: 100%;height: 40px;justify-content: flex-start;align-items: center;padding-left: 10px;border-bottom: 1px solid #ccc;">

        <div>常见问题</div>

    </div>
    <!--main List-->
    <div style="display: flex;flex-direction: column;width: 100%;">
        <!--list-->
        <?php foreach ($data as $key => $value): ?>
            <a href="http://backend.meishuquanyunxiao.com/wenti/info.html?id=<?=$value['id'];?>" style='text-decoration:none'>
              <div style="display: flex;flex-direction: row;justify-content: space-between;align-items: center;height: 40px;border-bottom: 1px solid #ccc;padding-left: 10px;padding-right: 10px">
                   <div><?= $value['title'];?></div>
                   <div> > </div>
              </div>
            </a>
        <?php endforeach;?>
    </div>

    </body>
 <?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>