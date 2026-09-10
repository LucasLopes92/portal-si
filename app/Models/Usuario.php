<?php
namespace Portal\Models;
use PDO;
final class Usuario { public function __construct(private PDO $db) {} public function findByEmail(string $email): ?array { $s=$this->db->prepare('SELECT * FROM usuarios WHERE email=:email AND ativo=true'); $s->execute(['email'=>$email]); return $s->fetch() ?: null; } public function create(string $nome,string $email,string $senha): void { $s=$this->db->prepare("INSERT INTO usuarios (nome,email,senha_hash,perfil) VALUES (:nome,:email,:senha,'aluno')"); $s->execute(['nome'=>$nome,'email'=>$email,'senha'=>password_hash($senha,PASSWORD_BCRYPT)]); } }
