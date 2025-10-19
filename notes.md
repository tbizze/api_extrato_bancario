Ideia:
Trabalhar com dois recursos com o PagSeguro:

1. Baixar transações diariamente, de forma automatizada, salvando no DB.
   Nesse caso, será do tipo transacional, obtendo diariamente todas as operações realizadas no dia anterior.

2. Consultar transações, de forma manual, escolhendo período e tipo de consulta.
   Nesse caso, pode ser transacional (quando ocorreu a operação) ou financeira (quando a operação é liberada em estrato).

3. Podemos fazer também uma conciliação, onde ao baixar a financeira, pelo 'codigo_transacao' localize aquele lançamento previsionado, e confirme taxas e data de liberação em conta.

Precisamos separar a lógica de consultar transações da lógica de salvar TXT, JSON e DB.
Assim, no futuro, quando precisar atualizar/modificar uma única alteração já serve pra todos.

- em TransactionManagerService, criar o método importAutomaticPagbank para realizar o auto import do Pagbank: transações e operações financeiras.
- fetchAllTransactions: traz todas as transações (previsão, algumas ainda não liberadas pelo banco, como crédito - 30 dias; ou débito, 1 dia). Argumentos: conta e data.
- fetchAllFinancials: traz todas as transações (liberadas pelo banco, crédito na conta). Argumentos: conta e data.
- saveTransactionsJson: Salva as transações em arquivo JSON. Argumentos: transações e conta.
- saveTransactionsToTxt: Salva as transações em arquivo TXT, de forma mais inteligível. Argumentos: transações e conta.
- getLeitor: método retorna o nome do leitor conforme número de série.