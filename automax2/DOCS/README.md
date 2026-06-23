# AutoMax — SGF Sistema de Gestão de Ferramentaria / Oficina

## Estrutura do Projeto (PSR-4 / MVC)

```
sgf_sistema/
├── app/                        ← Núcleo protegido (não exposto ao servidor)
│   ├── config/
│   │   ├── Database.php        ← Provedor PDO unificado (DIP)
│   │   ├── Auth.php            ← Funções de autenticação e sessão
│   │   └── Layout.php          ← renderHeader() / renderFooter()
│   ├── controllers/
│   │   ├── DashboardController.php
│   │   ├── ClienteController.php
│   │   ├── VeiculoController.php
│   │   ├── PecaController.php
│   │   └── OrdemController.php
│   ├── models/
│   │   ├── Cliente.php
│   │   ├── Veiculo.php
│   │   ├── Peca.php
│   │   └── OrdemServico.php
│   └── views/
│       ├── dashboard/index.php
│       ├── clientes/{lista,form}.php
│       ├── veiculos/{lista,form}.php
│       ├── pecas/{lista,form}.php
│       └── ordens/{lista,nova,detalhe}.php
│
├── public/                     ← Único diretório exposto ao servidor web
│   ├── .htaccess
│   ├── index.php               ← Front Controller / Dashboard
│   ├── login.php
│   ├── logout.php
│   ├── clientes.php
│   ├── veiculos.php
│   ├── pecas.php
│   ├── ordens.php
│   ├── relatorios.php
│   ├── css/estilo.css
│   └── js/dashboard.js
│
├── vendor/
│   └── autoload.php            ← Autoloader PSR-4 manual
│
├── uploads/
│   └── veiculos/               ← Fotos de veículos (fora do public)
│
├── DOCS/
│   ├── README.md               ← Este arquivo
│   └── automax.sql             ← Script de criação do banco
│
└── .htaccess                   ← Bloqueia acesso direto a app/, vendor/…
```

## Configuração (XAMPP)

1. Copie a pasta `sgf_sistema/` para `C:\xampp\htdocs\`
2. Importe `DOCS/automax.sql` no MySQL (phpMyAdmin ou linha de comando)
3. Ajuste credenciais em `app/config/Database.php` se necessário
4. Acesse: `http://localhost/sgf_sistema/public/`

## Princípios Aplicados

- **SRP** — Cada classe tem responsabilidade única (Model ≠ Controller ≠ View)
- **OCP** — Infraestrutura extensível sem alterar classes core
- **DIP** — Controllers recebem a conexão via `Database::getConnection()`, não instanciam PDO diretamente

## Usuários de Teste

| Login    | Senha | Perfil        |
|----------|-------|---------------|
| antonio  | 123   | Gerente       |
| luciana  | 123   | Recepcionista |
| jonas    | 123   | Mecânico      |
