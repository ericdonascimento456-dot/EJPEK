// 1. For que conta de 1 a 10
console.log("1. For - Contagem de 1 a 10:");
for (let i = 1; i <= 10; i++) {
    console.log(i);
}

// 2. For regressivo de 10 até 1
console.log("\n2. For - Contagem regressiva de 10 até 1:");
for (let i = 10; i >= 1; i--) {
    console.log(i);
}

// 3. While - Duplicando o saldo até passar de 100
console.log("\n3. While - Duplicando saldo:");
let saldo = 10;

while (saldo <= 100) {
    console.log(`Saldo atual: ${saldo}`);
    saldo = saldo * 2;
}
console.log(`Saldo final: ${saldo} (passou de 100)`);

// 4. For com break ao chegar no número 13
console.log("\n4. For com break no número 13:");
for (let i = 1; i <= 20; i++) {
    console.log(i);

    if (i === 13) {
        console.log("→ Parando no número 13 com break...");
        break;
    }
}

// 5. Array de produtos com laço (for clássico + for...of)
console.log("\n5. Array de produtos:");
let produtos = ["Notebook", "Mouse", "Teclado", "Monitor", "Celular", "Fone"];

console.log("Usando for clássico:");
for (let i = 0; i < produtos.length; i++) {
    console.log(produtos[i]);
}

console.log("\nUsando for...of (mais moderno):");
for (let produto of produtos) {
    console.log(produto);
}