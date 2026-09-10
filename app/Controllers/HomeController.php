<?php
namespace Portal\Controllers;
use Portal\Models\{Conteudo,Evento};
final class HomeController { public function __construct(private Conteudo $conteudos,private Evento $eventos) {} public function index(): void { view('public/home',['conteudos'=>$this->conteudos->published(6),'eventos'=>$this->eventos->upcoming(4)]); } public function article(string $slug): void { $conteudo=$this->conteudos->findPublished($slug); if(!$conteudo){http_response_code(404); view('public/not-found'); return;} view('public/article',compact('conteudo')); } }
