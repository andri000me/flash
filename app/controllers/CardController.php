<?php
use Phalcon\Http\Request;

use App\Forms\CreateCardForm;

class CardController extends ControllerBase
{
    public $createDeckForm;
    public $deckModel;
    public $cardModel;
    
    public function indexAction()
    {
        $card = Cards::find();
        $this->view->title = "Manage Cards";
        $this->view->cards = $cards;
        // dd($users);
    }

    public function initialize()
    {

        $this->authorized();
        $this->createCardForm = new CreateCardForm();
        $this->deckModel = new Decks();
        $this->cardModel = new Cards();
    }


    public function createAction()
    {
        $this->tag->setTitle('Phalcon :: Add Deck');

        $this->view->form = new CreateCardForm();
    }

    public function createSubmitAction()
    {
        
        // if (!$this->request->isPost()) {
        //     return $this->response->redirect('user/login');
        // }

        $this->createCardForm->bind($_POST, $this->cardModel);

        if (!$this->createCardForm->isValid()) {
            foreach ($this->createCardForm->getMessages() as $message) {
                $this->flash->error($message);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'create',
                ]);
                return;
            }
        }
        
        // // PENTING!
        // // Set User ID
        // var_dump($this->session->get('AUTH_ID'));
        $this->cardModel->setUserId($this->session->get('AUTH_ID'));
        $this->cardModel->setDeckId($this->session->get('DECK_ON'));

        if (!$this->cardModel->save()) {
            foreach ($this->cardModel->getMessages() as $m) {
                $this->flash->error($m);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'create',
                ]);
                return;
            }
        }

        $this->flash->success('Card created');
        return $this->response->redirect('user/profile/deck/'.$this->cardModel->getDeckId());

        // $this->view->cardsData = $cards;

        // $this->view->disable();
    }

    public function deleteAction($cardId)
    {
        // $id = (int)$deckId;

        $conditions = ['id'=>$cardId];
        $card = Cards::findFirst([
            'conditions' => 'id=:id:',
            'bind' => $conditions,
        ]);
        if ($card->delete() === false) {
            $messages = $card->getMessages();
            foreach ($messages as $message) {
                $this->flash->error($message);
            }
        } else {
            return $this->response->redirect('user/profile/deck/'.$this->session->get('DECK_ON'));
            // $this->view->pick('card/show');
        }
    }

    public function showAction($deckId)
    {

        $this->authorized();
        
        $conditions = ['id'=>$deckId];
        $deck = Decks::findFirst([
            'conditions' => 'id=:id:',
            'bind' => $conditions,
        ]);

        $cards = Cards::find([
            'conditions' => 'user_id = ?1 AND deck_id = ?2',
            'bind' => [
                1 => $this->session->get('AUTH_ID'),
                2 => $deck->id,
            ],
        ]);

        // echo $deck->title;
        // exit;
        $this->session->set('DECK_ON', $deck->id);

        $this->view->title = "Phalcon - Deck";
        $this->view->deck = $deck;
        $this->view->cardsData = $cards;

        $this->view->pick('card/show');

    }

    public function openAction($deckId, $cardId)
    {

        $this->authorized();
        
        // echo "OEPN woy";
        $conditions = ['idDeck'=>$deckId, 'idCard'=>$cardId];

        // echo $deckId;
        // echo $cardId;
        // $id = $this->view->getPa
        
        $card = Cards::find([
            'conditions' => 'user_id = ?1 AND deck_id = ?2 AND id = ?3',
            'bind' => [
                1 => $this->session->get('AUTH_ID'),
                2 => $this->session->get('DECK_ON'),
                3 => $cardId,
            ],
        ]);

        $this->view->cardsData = $card;

        // exit;
    }

}