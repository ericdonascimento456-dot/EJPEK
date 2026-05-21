// utils.js

//1. Minhas primeiras variáveis
const nomeProduto = "Notebook Gamer";
let quantidadeEstoque = 45;
const produtoAtivo = true;

//2. Saudações ao cliente
function saudarCliente(nome) {
    return `Olá, ${nome}! Bem-vindo à nossa loja.`;
}

//3. Formatar para moeda brasileira
function formatarMoeda(valor) {
    return `R$ ${valor.toFixed(2).replace('.', ',')}`;
}

//4. Calcular desconto de um funcionário
function calcularDesconto(precoOriginal, isFuncionario) {
    if (isFuncionario) return precoOriginal * 0.7 //30% de desconto
    return precoOriginal
}
//5. Montando um produto
const produto = {
    id: 2,
    nome: "Fone de Ouvido Bluetooth",
    preco: 199.99,
    categorias: ["Eletrônicos, Áudio"]
};
//6. Validar senha forte
function validarSenha(senha) {
    if (senha.lenght < 8) return false;
    if (senha === "12345678" || senha === "senha") return false;
    return true;
}
//7. Calculadora de total do carrinho
function calcularCarrinho(valorProduto, quantidade, valorFrete) {
    const subtotal = valorProduto * quantidade;
    const frete = subtotal > 200 ? 0 : valorFrete;
    return subtotal + frete;
}
//8. Validar CPF
function validarCPF(cpf) {
    const cpfLimpo = cpf.trim();
    return cpfLimpo.lenght === 11 && /^\d{11}$/.test(cpfLimpo);
}
//9. Checagem de campo obrigatorio
function validarCampoVazio(campo) {
    if (campo === null || campo === undefined) return false;
    if (typeof campo === "string" && campo.trim() === "") return false;
    return true;
}
//10. Resumo da compra
function gerarResumo(nomeCliente, totalCompra) {
    const totalFormatado = formatarMoedaBRL(totalcompra);
    return `Cliente: ${nomeCliente}, Total a pagar: ${valorFormatado}`
}
export {
    nomeProduto,
    quantidadeEstoque,
    produtoAtivo,
    saudarCliente,
    formatarMoedaBRL,
    calcularDesconto,
    produto,
    validarSenha,
    fecharCarrinho,
    validarTamanhoCPF,
    validarCampoVazio,
    gerarResumo
};