<?php
namespace Portal\Controllers;
use Portal\Models\Evento;
final class EventoController { public function __construct(private Evento $eventos) {} public function index(): void { if(empty($_SESSION['usuario'])) { header('Location: '.url('login')); exit; } view('admin/eventos',['eventos'=>$this->eventos->all()]); } }
