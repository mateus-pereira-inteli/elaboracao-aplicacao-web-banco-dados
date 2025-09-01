# Sistema de Vendas

## Funcionalidades Adicionadas

### SalePage.php
- **Registro de Vendas**: Formulário completo para registrar vendas
- **Relacionamento de Dados**: Vincula vendas aos funcionários através de chave estrangeira
- **Listagem com JOIN**: Exibe vendas com informações dos funcionários
- **Campos do Sistema**:
  - Seleção de Funcionário (busca da tabela EMPLOYEES)
  - Seleção de Produto (lista pré-definida)
  - Valor da venda (em reais)
  - Quantidade
  - Status da venda (finalizada/pendente)

## Estrutura do Banco de Dados

### Tabela SALES
- `ID` - Chave primária (auto incremento)
- `EMPLOYEE_ID` - Chave estrangeira para EMPLOYEES
- `PRODUCT` - Nome do produto (VARCHAR)
- `VALUE` - Valor unitário (INT)
- `QUANTITY` - Quantidade (INT)
- `COMPLETED` - Status da venda (BOOLEAN)
- `CREATED_AT` - Data/hora de criação (TIMESTAMP)

## Demonstração

[Link para vídeo demonstrativo](https://drive.google.com/file/d/1idUtHC0qm5MviiQdon0T0HeQoX_g6lWM/view?usp=sharing)