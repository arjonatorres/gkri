<?php

namespace app\controllers\comments;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii2mod\comments\controllers\DefaultController as BaseDefaultController;
use app\models\CommentModel;
use yii2mod\comments\events\CommentEvent;
use app\models\VotoComentario;
use yii\helpers\Url;

/**
 * Clase DefaultController
 *
 * @package app\comments\controllers
 */
class DefaultController extends BaseDefaultController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['quick-edit', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['quick-edit'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->identity->isAdmin) {
                                return true;
                            }

                            $comentario = CommentModel::find()
                                ->where(['id' => Yii::$app->request->get('id')])
                                ->one();

                            return
                            ($comentario->createdBy == Yii::$app->user->id)
                            &&
                            (!$comentario->tieneHijos());
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post', 'delete'],
                ],
            ],
            'contentNegotiator' => [
                'class' => 'yii\filters\ContentNegotiator',
                'only' => ['create'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Borra un comentario
     *
     * @param int $id Comment ID
     *
     * @return string Comment text
     */
    public function actionDelete($id)
    {
        $commentModel = $this->findModel($id);

        $event = Yii::createObject(['class' => CommentEvent::class, 'commentModel' => $commentModel]);
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);

        if ($commentModel->status == 2) {
            return Yii::t('yii2mod.comments', 'Comment Root has been deleted so this child was deleted too. Refresh to see the changes.');
        }


        if ($commentModel->markRejected()) {
            $this->trigger(self::EVENT_AFTER_DELETE, $event);

            return Yii::t('yii2mod.comments', 'Comment has been deleted.');
        } else {
            Yii::$app->response->setStatusCode(500);

            return Yii::t('yii2mod.comments', 'Comment has not been deleted. Please try again!');
        }
    }

    /**
     * Efectua el proceso de votar un comentario
     * @param  int $id        el id del comentario
     * @param  bool $positivo si es o no positivo el voto
     * @return int            el numero de votos totales del comentario
     */
    public function actionVotar($id, $positivo)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(Url::toRoute(['/user/login']));
        }

        $positivo = $positivo === 'true';
        $comentario = $this->findModel($id);
        $voto = new VotoComentario();
        $usuarioId = Yii::$app->user->id;

        $votoGuardadoB = VotoComentario::findOne(['usuario_id' => $usuarioId, 'comentario_id' => $id, 'positivo' => $positivo]);

        if ($votoGuardadoB) {
            $votoGuardadoB->delete();
            return $comentario->getVotosTotal();
        }

        $votoGuardado = VotoComentario::findOne(['usuario_id' => $usuarioId, 'comentario_id' => $id]);

        if ($votoGuardado) {
            $votoGuardado->delete();
        }

        $voto->usuario_id = $usuarioId;
        $voto->comentario_id = $id;
        $voto->positivo = $positivo;

        $voto->save();
        return $comentario->getVotosTotal();
    }
}
