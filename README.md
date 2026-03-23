# Conecta-Escola (NetFriends)

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.0+-blue.svg)](https://www.php.net/)

Uma rede social simples e intuitiva para alunos se conectarem, compartilhar posts, seguir amigos e interagir com curtidas e comentários. Desenvolvida em PHP puro com MySQL.

## ✨ Funcionalidades

- **🔐 Cadastro e Login**: Crie uma conta com nome, email, handle (@usuario) e senha segura.
- **👤 Perfil de Usuário**: Visualize e edite seu perfil, incluindo upload de foto de avatar.
- **📝 Posts**: Publique textos no feed principal e compartilhe suas ideias.
- **❤️ Curtidas e 💬 Comentários**: Interaja com posts de outros usuários.
- **👥 Seguir/Deseguir**: Siga outros alunos para ver seus posts no seu feed personalizado.
- **🔍 Busca**: Encontre alunos rapidamente por nome.
- **🏠 Feed**: Veja posts de usuários que você segue em tempo real.

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7+
- **Banco de Dados**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Servidor**: XAMPP (Apache + MySQL)
- **Outros**: PDO para conexões seguras com o banco

## 🚀 Instalação

Siga estes passos para configurar o projeto localmente:

1. **Instale o XAMPP**:
   - Baixe e instale o XAMPP do site oficial: [https://www.apachefriends.org/](https://www.apachefriends.org/).

2. **Clone ou baixe o projeto**:
   - Clone este repositório: `git clone https://github.com/seu-usuario/conecta-escola.git`
   - Ou baixe o ZIP e extraia para `C:\xampp\htdocs\conecta-escola`.

3. **Configure o banco de dados**:
   - Inicie o XAMPP e ative os módulos **Apache** e **MySQL**.
   - Acesse o phpMyAdmin em [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
   - Crie um novo banco de dados chamado `conecta_escola`.
   - Importe o arquivo `database/conecta_escola.sql` para criar as tabelas necessárias.

4. **Configure a conexão com o banco**:
   - Abra o arquivo `config/db.php` e verifique as credenciais:
     ```php
     $host = 'localhost';
     $db = 'conecta_escola';
     $user = 'root';
     $pass = ''; // Deixe vazio se não houver senha
     ```

5. **Acesse a aplicação**:
   - Abra seu navegador e vá para: [http://localhost/conecta-escola/public/index.php](http://localhost/conecta-escola/public/index.php).

## 📖 Como Usar

- **Página Inicial**: Faça login com suas credenciais ou cadastre-se se for novo usuário.
- **Feed**: Navegue pelos posts dos usuários que você segue.
- **Perfil**: Acesse seu perfil para editar informações ou visualizar posts próprios.
- **Busca**: Use a barra de busca para encontrar colegas.
- **Logout**: Clique em "Sair" na navegação para encerrar a sessão.

## 📁 Estrutura do Projeto

```
conecta-escola/
├── public/                 # Páginas públicas (login, cadastro, perfil, etc.)
├── controllers/            # Controladores PHP para lógica de negócio
├── models/                 # Modelos de dados (Usuario, Post)
├── config/                 # Configurações do banco de dados
├── assets/                 # CSS, JavaScript e imagens estáticas
├── uploads/                # Arquivos enviados (fotos de perfil)
├── database/               # Arquivo SQL do banco de dados
└── README.md               # Este arquivo
```

## 🤝 Contribuição

Contribuições são bem-vindas! Siga estes passos:

1. Faça um fork do projeto.
2. Crie uma branch para sua feature: `git checkout -b feature/nova-funcionalidade`.
3. Commit suas mudanças: `git commit -m 'Adiciona nova funcionalidade'`.
4. Push para a branch: `git push origin feature/nova-funcionalidade`.
5. Abra um Pull Request.

### Diretrizes
- Mantenha o código limpo e comentado.
- Teste suas mudanças antes de submeter.
- Siga as convenções de nomenclatura do projeto.

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE) - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 📞 Contato

- **Autor**: João cappeletti e Pietro
- **Email**: joao.cappeletti@aluno.senai.br e pietro.sehna@aluno.senai.br
- **Issues**: [Abra uma issue](https://github.com/seu-usuario/conecta-escola/issues) para relatar bugs ou sugerir melhorias.

---

⭐ Se gostou do projeto, dê uma estrela no GitHub!
