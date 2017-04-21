<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii2mod\editable\Editable;

// TODO cuando el admin borra un comentario con hijos, el borrar del hijo da error
/* @var $this \yii\web\View */
/* @var $model \yii2mod\comments\models\CommentModel */
/* @var $maxLevel null|integer comments max level */
?>
<li class="comment" id="comment-<?php echo $model->id; ?>">
    <div class="comment-content" data-comment-content-id="<?php echo $model->id ?>">
        <div class="comment-author-avatar">
            <?php echo Html::img($model->getAvatar(), ['alt' => $model->getAuthorName(), 'width' => '50', 'height' => '50']); ?>
        </div>
        <div class="comment-details">
            <div class="comment-action-buttons">
                <?php if (Yii::$app->user->isGuest) :?>
                <?php elseif (Yii::$app->user->identity->isAdmin) :?>
                    <?php echo Html::a('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('yii2mod.comments', 'Delete'), '#', ['data' => ['action' => 'delete', 'url' => Url::to(['/comment/default/delete', 'id' => $model->id]), 'comment-id' => $model->id]]); ?>
                <?php elseif (!$model->isChild() && !$model->tieneHijos() && ($model->createdBy == Yii::$app->user->identity->id)) :?>
                    <?php echo Html::a('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('yii2mod.comments', 'Delete'), '#', ['data' => ['action' => 'delete', 'url' => Url::to(['/comment/default/delete', 'id' => $model->id]), 'comment-id' => $model->id]]); ?>
                <?php elseif ($model->isChild() && ($model->createdBy == Yii::$app->user->identity->id)) :?>
                    <?php echo Html::a('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('yii2mod.comments', 'Delete'), '#', ['data' => ['action' => 'delete', 'url' => Url::to(['/comment/default/delete', 'id' => $model->id]), 'comment-id' => $model->id]]); ?>
                <?php endif; ?>

                <?php if (!Yii::$app->user->isGuest && ($model->level < $maxLevel || is_null($maxLevel))) : ?>
                    <?php echo Html::a("<span class='glyphicon glyphicon-share-alt'></span> " . Yii::t('yii2mod.comments', 'Reply'), '#', ['class' => 'comment-reply', 'data' => ['action' => 'reply', 'comment-id' => $model->id]]); ?>
                <?php endif; ?>
            </div>
            <div class="comment-author-name">
                <span><?php echo $model->getAuthorName(); ?></span>
                <?php echo Html::a($model->getPostedDate(), $model->getAnchorUrl(), ['class' => 'comment-date']); ?>
            </div>
            <div class="comment-body">
                <?php if (Yii::$app->getModule('comment')->enableInlineEdit): ?>
                    <?php echo Editable::widget([
                        'model' => $model,
                        'attribute' => 'content',
                        'url' => '/comment/default/quick-edit',
                        'options' => [
                            'id' => 'editable-comment-' . $model->id,
                        ],
                    ]); ?>
                <?php else: ?>
                    <?php echo $model->getContent(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</li>
<?php if ($model->hasChildren()) : ?>
    <ul class="children">
        <?php foreach ($model->getChildren() as $children) : ?>
            <li class="comment" id="comment-<?php echo $children->id; ?>">
                <?php echo $this->render('_list', ['model' => $children, 'maxLevel' => $maxLevel]) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
