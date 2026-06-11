select * from produtos;
select * from membros;
select * from usuarios;
select * from emprestimos;

ALTER TABLE usuarios ADD COLUMN status_ativo BOOLEAN DEFAULT true;
