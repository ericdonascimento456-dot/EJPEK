//1. switch dia de semana
let diaDaSemana = 2;

switch (diaDaSemana) {
    case 1:
        console.log("Domingo");
        break;
    case 2:
        console.log("Segunda-feira");
        break;
    case 3:
        console.log("Terça-feira");
        break;
    case 4:
        console.log("Quarta-feira");
        break;
    case 5:
        console.log("Quinta-feira");
        break;
    case 6:
        console.log("Sexta-feira");
        break;
    case 7:
        console.log("Sábado");
        break;
    default:
        console.log("Dia inválido");
}
//2. tirar break 
let diaDaSemana2 = 2;

switch (diaDaSemana2) {
    case 1:
        console.log("Domingo");
        break;
    case 2:
        console.log("Segunda-feira");
        //break tirado intencionalmente
    case 3:
        console.log("Terça-feira");
        break;
    case 4:
        console.log("Quarta-feira");
        break;
    case 5:
        console.log("Quinta-feira");
        break;
    case 6:
        console.log("Sexta-feira");
        break;
    case 7:
        console.log("Sábado");
        break;
    default:
        console.log("Dia inválido");
}

let diaDaSemana3 = "2";

switch (diaDaSemana3) {
    case 1:
        console.log("Domingo");
        break;
    case 2:
        console.log("Segunda-feira");
        break;
    case 3:
        console.log("Terça-feira");
        break;
    case 4:
        console.log("Quarta-feira");
        break;
    case 5:
        console.log("Quinta-feira");
        break;
    case 6:
        console.log("Sexta-feira");
        break;
    case 7:
        console.log("Sábado");
        break;
    default:
        console.log("Dia inválido");
}

//É possivel por mais de uma case na msm linha?
let diaDaSemana4 = 6;

switch (diaDaSemana4) {
    case 1:
    case 7: // agrupamento: Domingo e Sábado
        console.log("Fim de semana!");
        break;
    case 2:
    case 3:
    case 4:
    case 5:
    case 6: // agrupamento: dias úteis
        console.log("Dia útil");
        break;
    default:
        console.log("Dia inválido");
}