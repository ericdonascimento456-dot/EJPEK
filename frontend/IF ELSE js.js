//1. Verificar se é maior de idade
let idadeUsuario = "";

if (idadeUsuario >= 18) {
    console.log("Você é maior de idade ");

    //2. Se idade <18 então 

} else {
    console.log("Você é menor de idade");
}

//3. Se tiver mais de 60 anos === idoso

if (idadeUsuario >= 60) {
    console.log("Você é idoso, vá para o acento preferencial. ");
} else {
    console.log("Você não é idoso. ");
}

//4. Idade 0, o que acontece?
if (idadeUsuario === 0) {
    console.log("Erro")
}

//5. Ternário

let idade = "";
let verificarIdade = (idade >= 18) ? "Maior de idade" : "Menor de Idade";
console.log("Você é: " + verificarIdade);