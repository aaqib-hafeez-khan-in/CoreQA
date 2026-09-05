<?php
namespace App\Controllers;
use App\Core\View;
use App\Models\{Topic,Post,User};

class PanelController {
  public function __construct(private \App\Core\Container $c){}

  public function me(){
    $auth=$this->c->get('auth'); $auth->requireLogin();
    $auth->refreshUser();
    $db=$this->c->get('db'); $uid=$auth->id();
    View::render('panel/me',[
      'title'=>'My Space',
      'my_topics'=>Topic::byUser($db,$uid,12),
      'my_posts'=>Post::byUser($db,$uid,12),
      'profile'=>User::findById($db,$uid),
      'csrf'=>$this->c->get('csrf'),
      'auth'=>$auth,
    ]);
  }

  public function updateAccount(){
    $auth=$this->c->get('auth'); $auth->requireLogin();
    if(!$this->c->get('csrf')->validate($_POST['_token']??'')) exit('CSRF');
    $id=$auth->id();
    $name=trim($_POST['name']??'');
    $email=strtolower(trim($_POST['email']??''));
    if($id===null || $name==='' || strlen($name)>120 || !filter_var($email,FILTER_VALIDATE_EMAIL)){
      $_SESSION['flash']='Please provide valid account details.';
      header('Location: /me');
      return;
    }
    if(User::emailExistsForOther($this->c->get('db'),$email,$id)){
      $_SESSION['flash']='Email already in use.';
      header('Location: /me');
      return;
    }
    User::updateAccount($this->c->get('db'),$id,$name,$email);
    $auth->refreshUser();
    $_SESSION['flash']='Account details updated.';
    header('Location: /me');
  }

  public function updatePassword(){
    $auth=$this->c->get('auth'); $auth->requireLogin();
    if(!$this->c->get('csrf')->validate($_POST['_token']??'')) exit('CSRF');
    $id=$auth->id();
    $current=$_POST['current_password']??'';
    $new=$_POST['new_password']??'';
    $confirm=$_POST['new_password_confirmation']??'';
    $user=$id===null?null:User::byEmail($this->c->get('db'),$auth->user()['email']);
    if($id===null || !$user || !password_verify($current,$user['password_hash']) || strlen($new)<12 || !hash_equals($new,$confirm)){
      $_SESSION['flash']='Please check your current password and new password details.';
      header('Location: /me');
      return;
    }
    User::updatePassword($this->c->get('db'),$id,$new);
    session_regenerate_id(true);
    $auth->refreshUser();
    $_SESSION['flash']='Password updated.';
    header('Location: /me');
  }

  public function mod(){
    $this->c->get('auth')->requireRole('mod');
    $db=$this->c->get('db');
    View::render('panel/mod',[
      'title'=>'Moderation',
      'topics'=>Topic::paginated($db,null,null,1,50),
      'posts'=>Post::recentWithScore($db,50),
      'csrf'=>$this->c->get('csrf')
    ]);
  }

  public function toggleTopic(int $id){
    $this->c->get('auth')->requireRole('mod');
    if(!$this->c->get('csrf')->validate($_POST['_token']??'')) exit('CSRF');
    Topic::toggle($this->c->get('db'),$id);
    header('Location: /panel/mod');
  }

  public function resetVotes(int $postId){
    $this->c->get('auth')->requireRole('mod');
    if(!$this->c->get('csrf')->validate($_POST['_token']??'')) exit('CSRF');
    Post::resetVotes($this->c->get('db'),$postId);
    header('Location: /panel/mod');
  }

  public function deletePost(int $postId){
    $this->c->get('auth')->requireRole('mod');
    if(!$this->c->get('csrf')->validate($_POST['_token']??'')) exit('CSRF');
    Post::delete($this->c->get('db'),$postId);
    $_SESSION['flash'] = 'Comment deleted successfully';
    $referer = $_SERVER['HTTP_REFERER'] ?? '/panel/mod';
    header('Location: ' . $referer);
  }
}
