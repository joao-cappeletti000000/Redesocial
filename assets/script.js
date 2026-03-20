const mongoose = require("mongoose");

// 🔗 conecta no MongoDB
mongoose.connect("mongodb+srv://joaocappeletti:232528La@cluster0.ukqoezj.mongodb.net/?appName=Cluster0")
  .then(() => console.log("✅ Conectado ao MongoDB"))
  .catch(err => console.log("❌ Erro:", err));

// 📦 cria um modelo (tipo tabela)
const Usuario = mongoose.model("Usuario", {
  nome: String,
  idade: Number
});

// ➕ criar e salvar um usuário
async function criarUsuario() {
  const novoUsuario = new Usuario({
    nome: "Cappeletti",
    idade: 16
  });

  await novoUsuario.save();
  console.log("💾 Usuário salvo!");
}

// 🔍 buscar usuários
async function listarUsuarios() {
  const usuarios = await Usuario.find();
  console.log("📋 Lista:", usuarios);
}

// ▶️ executar tudo
async function executar() {
  await criarUsuario();
  await listarUsuarios();
}

executar();