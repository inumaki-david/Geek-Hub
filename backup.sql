--
-- PostgreSQL database dump
--

\restrict akkuX0fzhQgkWGcn7967YBHLPAmoeJCkkNijl9EdPhJ5VgteDj7KeI5DJbwwCZW

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: emprestimos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.emprestimos (
    id integer NOT NULL,
    produto_id integer NOT NULL,
    membro_id integer NOT NULL,
    usuario_id integer NOT NULL,
    data_inicio timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    data_fim_prevista date NOT NULL,
    data_devolucao date,
    valor_diaria_cobrado numeric(10,2) NOT NULL,
    multa_aplicada numeric(10,2) DEFAULT 0.00,
    status character varying(20) DEFAULT 'Pendente'::character varying
);


ALTER TABLE public.emprestimos OWNER TO postgres;

--
-- Name: emprestimos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.emprestimos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.emprestimos_id_seq OWNER TO postgres;

--
-- Name: emprestimos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.emprestimos_id_seq OWNED BY public.emprestimos.id;


--
-- Name: membros; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.membros (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    cpf character varying(14) NOT NULL,
    telefone character varying(20),
    status_ativo boolean DEFAULT true
);


ALTER TABLE public.membros OWNER TO postgres;

--
-- Name: membros_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.membros_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.membros_id_seq OWNER TO postgres;

--
-- Name: membros_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.membros_id_seq OWNED BY public.membros.id;


--
-- Name: produtos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.produtos (
    id integer NOT NULL,
    titulo character varying(150) NOT NULL,
    categoria character varying(50) NOT NULL,
    quantidade integer DEFAULT 0 NOT NULL,
    valor_diaria numeric(10,2) NOT NULL,
    disponivel boolean DEFAULT true
);


ALTER TABLE public.produtos OWNER TO postgres;

--
-- Name: produtos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.produtos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.produtos_id_seq OWNER TO postgres;

--
-- Name: produtos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.produtos_id_seq OWNED BY public.produtos.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    senha_hash character varying(255) NOT NULL,
    perfil_acesso character varying(20) NOT NULL,
    CONSTRAINT usuarios_perfil_acesso_check CHECK (((perfil_acesso)::text = ANY ((ARRAY['Gerente'::character varying, 'Comum'::character varying])::text[])))
);


ALTER TABLE public.usuarios OWNER TO postgres;

--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO postgres;

--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- Name: emprestimos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.emprestimos ALTER COLUMN id SET DEFAULT nextval('public.emprestimos_id_seq'::regclass);


--
-- Name: membros id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.membros ALTER COLUMN id SET DEFAULT nextval('public.membros_id_seq'::regclass);


--
-- Name: produtos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produtos ALTER COLUMN id SET DEFAULT nextval('public.produtos_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- Data for Name: emprestimos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.emprestimos (id, produto_id, membro_id, usuario_id, data_inicio, data_fim_prevista, data_devolucao, valor_diaria_cobrado, multa_aplicada, status) FROM stdin;
\.


--
-- Data for Name: membros; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.membros (id, nome, cpf, telefone, status_ativo) FROM stdin;
\.


--
-- Data for Name: produtos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.produtos (id, titulo, categoria, quantidade, valor_diaria, disponivel) FROM stdin;
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (id, nome, email, senha_hash, perfil_acesso) FROM stdin;
\.


--
-- Name: emprestimos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.emprestimos_id_seq', 1, false);


--
-- Name: membros_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.membros_id_seq', 1, false);


--
-- Name: produtos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.produtos_id_seq', 1, false);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 1, false);


--
-- Name: emprestimos emprestimos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.emprestimos
    ADD CONSTRAINT emprestimos_pkey PRIMARY KEY (id);


--
-- Name: membros membros_cpf_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.membros
    ADD CONSTRAINT membros_cpf_key UNIQUE (cpf);


--
-- Name: membros membros_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.membros
    ADD CONSTRAINT membros_pkey PRIMARY KEY (id);


--
-- Name: produtos produtos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produtos
    ADD CONSTRAINT produtos_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: emprestimos fk_membro; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.emprestimos
    ADD CONSTRAINT fk_membro FOREIGN KEY (membro_id) REFERENCES public.membros(id) ON DELETE RESTRICT;


--
-- Name: emprestimos fk_produto; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.emprestimos
    ADD CONSTRAINT fk_produto FOREIGN KEY (produto_id) REFERENCES public.produtos(id) ON DELETE RESTRICT;


--
-- Name: emprestimos fk_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.emprestimos
    ADD CONSTRAINT fk_usuario FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);


--
-- PostgreSQL database dump complete
--

\unrestrict akkuX0fzhQgkWGcn7967YBHLPAmoeJCkkNijl9EdPhJ5VgteDj7KeI5DJbwwCZW

