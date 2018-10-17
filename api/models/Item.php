<?php
class Item{

    public function get_user(){//TODO
        $user = $this->model('Users');
        $user->name = 'Arslan'; 

        $this->view('json-output',['output'=>$user->name]);
    }

    public function add_user(){//TODO
        $this->view('json-output',['output'=>'add_user']);
    }

    public function update_user(){//TODO
        $this->view('json-output',['output'=>'update_user']);
    }

    public function delete_user(){//TODO
        $this->view('json-output',['output'=>'delete_user']);
    }

}
