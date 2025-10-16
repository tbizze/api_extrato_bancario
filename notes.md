Criar uma tabela exclusiva para transações do PagSeguro:

id_transacao: F78965AC3EF34BAD9830AC44275138C4
dt_operacao: 2025-09-30 20:19:21
dt_pgto_prevista: 2025-09-30
valor: 100
tarifa: 0.99
descricao: Instituicao: BACEN - Parcelas: 0 - Meio Pagamento: 11
id_leitor: 1731279966
tipo: PIX

Ideia:
Trabalhar com dois recursos com o PagSeguro:

1. Baixar transações diariamente, de forma automatizada, salvando no DB.
   Nesse caso, será do tipo transacional, obtendo diariamente todas as operações realizadas no dia anterior.

2. Consultar transações, de forma manual, escolhendo período e tipo de consulta.
   Nesse caso, pode ser transacional (quando ocorreu a operação) ou financeira (quando a operação é liberada em estrato).

3. Podemos fazer também uma conciliação, onde ao baixar a financeira, pelo 'codigo_transacao' localize aquele lançamento previsionado, e confirme taxas e data de liberação em conta.

Precisamos separar a lógica de consultar transações da lógica de salvar TXT, JSON e DB.
Assim, no futuro, quando precisar atualizar/modificar uma única alteração já serve pra todos.

- fetchAllTransactions: traz todas as transações (previsão, algumas ainda não liberadas pelo banco, como crédito - 30 dias; ou débito, 1 dia). Argumentos: conta e data.
- fetchAllFinancials: traz todas as transações (liberadas pelo banco, crédito na conta). Argumentos: conta e data.
- saveTransactionsJson: Salva as transações em arquivo JSON. Argumentos: transações e conta.
- saveTransactionsToTxt: Salva as transações em arquivo TXT, de forma mais inteligível. Argumentos: transações e conta.
- getLeitor: método retorna o nome do leitor conforme número de série.