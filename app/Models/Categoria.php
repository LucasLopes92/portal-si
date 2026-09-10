<?php
namespace Portal\Models;
use PDO;
final class Categoria { public function __construct(private PDO $db) {} public function active(): array { return $this->db->query('SELECT * FROM categorias WHERE ativo=true ORDER BY ordem,nome')->fetchAll(); } }
