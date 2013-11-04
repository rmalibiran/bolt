<?php

if (!class_exists('TestUser')) {
    class TestUser implements ArrayAccess {
        public $username;
        public $password;
        public $roles;

        public function __construct($username, $password, $roles) {
            $this->username = $username;
            $this->password = $password;
            $this->roles = $roles;
        }

        public function __toString() {
            return "User '" . $this->username . "'";
        }

        public function offsetExists($offset) {
            return isset($this->$offset);
        }

        public function offsetGet($offset) {
            return $this->$offset;
        }

        public function offsetSet($offset, $value) {
            $this->$offset = $value;
        }

        public function offsetUnset($offset) {
            unset($this->$offset);
        }

        public function getHashedPassword() {
            // Magic value from default configuration.
            $hash_strength = 10;
            $hasher = new \Hautelook\Phpass\PasswordHash($hash_strength, true);
            $hashedPassword= $hasher->HashPassword($this->password);
            return $hashedPassword;
        }

        public function create(WebGuy $I, $id) {
            $I->haveInDatabase('bolt_users',
                    array(
                        'id' => $id,
                        'username' => $this->username,
                        'password' => $this->getHashedPassword(),
                        'email' => $this->username . '@example.org',
                        'lastseen' => '1900-01-01',
                        'lastip' => '',
                        'displayname' => ucwords($this->username),
                        'contenttypes' => serialize(array()),
                        'stack' => serialize(array()),
                        'enabled' => '1',
                        'userlevel' => '1',
                        'shadowpassword' => '',
                        'shadowtoken' => '',
                        'shadowvalidity' => '1900-01-01',
                        'failedlogins' => '0',
                        'throttleduntil' => '1900-01-01',
                        'roles' => json_encode($this->roles),
                        ));
        }
    }
}

$users = array(
    'admin' => new TestUser('admin', 'admin1', array('developer')),
    'bossdude' => new TestUser('bossdude', 'bossdude1', array('chief-editor')),
    'editor' => new TestUser('editor', 'editor1', array('editor')),
    'pagewriter' => new TestUser('pagewriter', 'pagewriter1', array('page-editor')),
    );

$I = new WebGuy($scenario);

// we'll start with ID = 2, because the database dump already has user #1.
$i = 2;
foreach ($users as $user) {
    $user->create($I, $i++);
}
