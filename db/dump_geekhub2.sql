--
-- PostgreSQL database dump
--

\restrict 6ivBbvNRNPdGCZb9RiOeroUqHA63rwmO0AIVbWIrpvNChBuImxcnvw5fYcrGy6g

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
-- Name: logsauditoria; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.logsauditoria (
    id integer NOT NULL,
    usuario_id integer,
    acao character varying(100) NOT NULL,
    descricao text NOT NULL,
    data_hora timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.logsauditoria OWNER TO postgres;

--
-- Name: logsauditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.logsauditoria_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.logsauditoria_id_seq OWNER TO postgres;

--
-- Name: logsauditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.logsauditoria_id_seq OWNED BY public.logsauditoria.id;


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
    imagem_capa character varying(255),
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
    status_ativo boolean DEFAULT true,
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
-- Name: logsauditoria id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logsauditoria ALTER COLUMN id SET DEFAULT nextval('public.logsauditoria_id_seq'::regclass);


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
2	2	14	1	2026-06-09 23:18:53.31715	2026-06-11	\N	5.49	0.00	Pendente
6	11	14	1	2026-06-10 21:20:54.507833	2026-06-12	\N	100.00	0.00	Pendente
4	7	14	1	2026-06-10 19:05:58.659563	2026-06-17	2026-06-10	9.95	0.00	Concluído
9	10	21	1	2026-06-11 08:12:43.471479	2026-06-12	\N	20.00	0.00	Pendente
10	11	21	1	2026-06-11 08:14:15.252961	2026-06-18	\N	100.00	0.00	Pendente
1	3	14	1	2026-06-09 23:16:42.776194	2026-06-17	2026-06-11	6.49	0.00	Concluído
11	8	18	1	2026-06-11 09:26:37.269655	2027-06-11	2026-06-11	11.50	0.00	Concluído
12	7	22	1	2026-06-12 18:22:58.85461	2026-06-13	\N	9.95	0.00	Pendente
13	9	24	1	2026-06-15 20:01:48.956883	2026-06-16	\N	10.00	0.00	Pendente
14	8	25	4	2026-06-15 22:37:20.057747	2026-06-17	\N	11.50	0.00	Pendente
8	9	21	1	2026-06-10 23:57:41.794798	2026-06-16	2026-06-15	10.00	0.00	Concluído
15	10	25	1	2026-06-15 23:01:51.775586	2026-06-17	\N	20.00	0.00	Pendente
\.


--
-- Data for Name: logsauditoria; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.logsauditoria (id, usuario_id, acao, descricao, data_hora) FROM stdin;
11	1	Cadastro de Produto	Cadastrou um novo item no acervo: Homem de Ferro 3 (Categoria: Filme (DVD/Blu-ray))	2026-06-15 23:33:51.234112
\.


--
-- Data for Name: membros; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.membros (id, nome, cpf, telefone, status_ativo) FROM stdin;
24	Ana Clara Andrade	159.753.456-56	(19) 55278-9340	t
14	Evelyn Levindo	123.456.789-01	(19) 99334-9723	f
18	Larissa Nicodemo	987.654.321-01	(19) 99334-9723	t
21	Davi Brenna	537.653.198-80	(19) 99334-9723	f
22	Larissa Vieira	357.245.644-78	(19) 55278-9340	f
25	Emily Guimarães	164.489.756-45	(19) 99334-9723	t
\.


--
-- Data for Name: produtos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.produtos (id, titulo, categoria, quantidade, valor_diaria, imagem_capa, disponivel) FROM stdin;
11	Pokémon Box Ogerpon Ex - Copag	Outro	0	80.00	../uploads/capas/capa_6a29ff5422378.webp	f
3	Jujutsu Kaisen V.04	Manga	20	6.69	../uploads/capas/capa_6a28ac5c3be59.webp	t
8	Star Wars IV - Uma Nova Esperança	Filme	14	11.50	../uploads/capas/capa_6a29eaaab2026.jpg	t
9	Pragmata - PS5	Jogo	19	10.00	../uploads/capas/capa_6a29f3e15a411.jpg	t
2	Jujutsu Kaisen V.01	Manga	10	5.49	../uploads/capas/capa_6a28abd45e3d5.webp	t
1	Jujutsu Kaisen V.09	Manga	20	7.49	../uploads/capas/capa_6a28a43e214c6.png	t
10	Box Pokémon Mega Charizard Y	Outro	1	20.00	../uploads/capas/capa_6a29f455e9f13.png	t
12	Duna - Parte I	Filme	12	9.55	../uploads/capas/capa_6a30b2a992187.jpg	t
13	Duna - Parte II	Filme	15	9.55	../uploads/capas/capa_6a30b40ad2b42.webp	t
14	Homem de Ferro 3	Filme	15	11.50	../uploads/capas/capa_6a30b60f36bd6.jpg	t
7	Star Wars II - O Ataque dos Clones	Filme	0	9.95	../uploads/capas/capa_6a29c852b49cc.jpg	f
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (id, nome, email, senha_hash, perfil_acesso, status_ativo) FROM stdin;
1	Davi Martins	davi@geekhub.com	$2y$12$BRJkG/nlFsl3W29F8Up5JOEJoqYhjrY6CE0uVrLn48dyzzebTDjPe	Gerente	t
4	Bruno	bruno@geekhub.com	$2y$12$R7fjK5ISpReBanKbnwexwuAuLfaSi.IKObt6OE3Xs65TgY9vc5gZe	Comum	t
\.


--
-- Name: emprestimos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.emprestimos_id_seq', 15, true);


--
-- Name: logsauditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.logsauditoria_id_seq', 11, true);


--
-- Name: membros_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.membros_id_seq', 25, true);


--
-- Name: produtos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.produtos_id_seq', 14, true);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 4, true);


--
-- Name: emprestimos emprestimos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.emprestimos
    ADD CONSTRAINT emprestimos_pkey PRIMARY KEY (id);


--
-- Name: logsauditoria logsauditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logsauditoria
    ADD CONSTRAINT logsauditoria_pkey PRIMARY KEY (id);


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
-- Name: logsauditoria logsauditoria_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logsauditoria
    ADD CONSTRAINT logsauditoria_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict 6ivBbvNRNPdGCZb9RiOeroUqHA63rwmO0AIVbWIrpvNChBuImxcnvw5fYcrGy6g

