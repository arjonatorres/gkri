<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\models\User;
use app\models\Categoria;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
$this->registerJsFile('@web/js/autocompletar.js', ['depends' => [\yii\web\JqueryAsset::className()], 'position' => View::POS_END]);
if (!Yii::$app->user->isGuest) {
    $this->registerJsFile('@web/js/notificaciones.js', ['depends' => [\yii\web\JqueryAsset::className()], 'position' => View::POS_END]);
    $this->registerJsFile('@web/js/mensajes.js', ['depends' => [\yii\web\JqueryAsset::className()], 'position' => View::POS_END]);

    $user = User::findOne(['id' => Yii::$app->user->id]);

    $conversaciones = $user->getConversaciones();
}

$js = <<<EOT
    var tour = localStorage.getItem('tour');

    if (tour === null) {
        if (location.pathname !== '/') {
            location.pathname = '/';
        } else {
            introJs()
            .setOption('showStepNumbers', false)
            .setOption('nextLabel', 'Siguiente')
            .setOption('prevLabel', 'Anterior')
            .setOption('skipLabel', 'Terminar Tour')
            .setOption('doneLabel', 'Terminar Tour')
            .setOption('overlayOpacity', 0.2)
            .start();
            localStorage.setItem('tour', 1);
        }
    }
EOT;
$this->registerJs($js);
$categorias = Categoria::find()->all();
$this->title = 'GKRI';
// TODO poner imagen en los mensajes
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <script src="https://use.fontawesome.com/a727822b2c.js"></script>
</head>
<body>
<div class="template-oculto">
    <div class="msg">
        <!-- <span class="chat-img pull-left"> -->
            <!-- <img src="http://placehold.it/50/55C1E7/fff&text=U" alt="User Avatar" class="img-circle" /> -->
        <!-- </span> -->
        <div class="msg-body">
            <div class="msg-header">
                <small class="text-muted">
                    <span class="glyphicon glyphicon-time"></span>
                </small>
            </div>
            <p></p>
        </div>
    </div>
</div>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Html::img('@web/images/logo.png', [
            'alt'=>Yii::$app->name,
            'class' => 'logo',
            'data-intro'=>"¡Bienvenido! Vamos a hacer un pequeño Tour para que no te pierdas ni un detalle.",
            'data-step'=>"1"
        ]),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);

    ?>
    <ul class="navbar-nav navbar-left nav" data-step="2" data-intro="Aquí están las distintas categorías, selecciona la que mas te guste y empieza a divertirte.">
        <li><a href="/gracioso">Gracioso</a></li>
        <li><a href="/amor">Amor</a></li>
        <li><a href="/series">Series</a></li>
        <li><a href="/wtf">WTF</a></li>
        <li class="dropdown">
            <a href="/" data-toggle="dropdown" class="dropdown-toggle">Más<b class="caret"></b></a>
            <ul class="dropdown-menu multi-column columns-3">
                <div class="row">
                    <?php foreach ($categorias as $i => $categoria) {
                        if ($i == 0) { ?>
                            <div class="col-sm-4">
                                <ul class="multi-column-dropdown">
                                    <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                        <?php }
                        elseif ($i == 5 || $i == 11) { ?>
                                    <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                                </ul>
                            </div>
                            <div class="col-sm-4">
                                <ul class="multi-column-dropdown">
                        <?php
                        } elseif ($i == count($categorias) - 1) { ?>
                                    <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                                </ul>
                            </div>
                        <?php
                        } else { ?>
                            <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                        <?php }
                    } ?>
                </div>
            </ul>
        </li>
    </ul>
   <ul class="navbar-nav navbar-right nav" data-step="3" data-intro="Aquí tendrás las distintas opciones del usuario cuando hayas iniciado sesión, como ver tu perfil, las notificaciones o incluso ¡subir tu propio post!">
       <li></li>
       <li class="dropdown">
           <a data-toggle="dropdown" class="dropdown-toggle"><i id="lupa" class="glyphicon glyphicon-search"></i></a>
           <ul class="dropdown-menu dropdown-menu-search">
               <li>
                   <?php ActiveForm::begin(['action' =>  ['/posts/search'], 'method' => 'get', 'options' => ['class' => 'navbar-form navbar-right','role' => 'search']]);?>
                   <input type="text" id="search" class="form-control" placeholder="Search" name="q">
                   <?php ActiveForm::end();?>
               </li>
           </ul>
       </li>
    <?php if (Yii::$app->user->isGuest) : ?>
        <li><a class="blanco" href="<?= Url::to('/user/security/login') ?>">Login</a></li>
        <li><a class="blanco" href="<?= Url::to('/user/register') ?>">Registrarse</a></li>
    <?php else :?>
        <li class="dropdown dropdown-notifications">
            <a data-toggle="dropdown" class="dropdown-toggle">
              <i data-count="0" class="glyphicon glyphicon-bell notification-icon hidden-icon"></i>
            </a>

            <div class="dropdown-container">

                <div class="dropdown-toolbar">
                    <div class="dropdown-toolbar-actions">
                        <a href="/" id="notification-all-read">Marcar todas como leídas</a>
                    </div>
                    <h3 class="dropdown-toolbar-title">Notificaciones</h3>
                </div>

                <ul class="dropdown-menu dropdown-notifications-list">

                </ul>
            </div>
        </li>
        <li class="dropdown">
            <a id="imagen-avatar" class="dropdown-toggle" href="/u/xharly8" data-toggle="dropdown">
                <?= Html::img(Yii::$app->user->identity->profile->getAvatar(), ['class' => 'img-rounded little']) ?>
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
                <li><a href="<?= Url::to('/u/' . Yii::$app->user->identity->username) ?> " tabindex="-1">Mi Perfil</a></li>
                <li><a href="<?= Url::to('/settings/profile') ?>" tabindex="-1">Configuración</a></li>
                <li><a href="javascript:void(0);" data-toggle="modal" data-target="#messages" tabindex="-1">Mensajes</a></li>
                <li class="divider"></li>
                <li><a href="<?= Url::to('/user/security/logout') ?>" data-method="post" tabindex="-1">Logout</a></li>
            </ul>
        </li>
        <li><a class="boton-upload btn-primary" href="<?= Url::to('/posts/upload') ?>">Upload</a></li>
    <?php endif; ?>
    </ul> <?php

    NavBar::end();

    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?php if (isset($conversaciones)) : ?>
        <?= $this->render('messages', ['conversaciones' => $conversaciones, 'user' => $user]) ?>
        <?php endif; ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; GKRI <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
