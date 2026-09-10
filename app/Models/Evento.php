<?php
namespace Portal\Models;
use PDO;
final class Evento { public function __construct(private PDO $db) {} public function upcoming(int $limit=6): array {$s=$this->db->prepare("SELECT e.*,u.nome autor FROM eventos e JOIN usuarios u ON u.id=e.autor_id WHERE e.status='publicado' AND e.data_inicio>=NOW() ORDER BY e.data_inicio LIMIT :limite");$s->bindValue('limite',$limit,PDO::PARAM_INT);$s->execute();return $s->fetchAll();} public function all(): array{return $this->db->query('SELECT e.*,u.nome autor FROM eventos e JOIN usuarios u ON u.id=e.autor_id ORDER BY e.data_inicio DESC')->fetchAll();} }
