from flask import Flask, render_template, request, redirect, url_for, flash, jsonify
from flask_login import LoginManager, UserMixin, login_user, login_required, logout_user, current_user
import mysql.connector
from datetime import datetime

app = Flask(__name__)
app.secret_key = 'chave_secreta_senai_2025_troque_em_producao'

from jinja2 import Environment

# Ativando os filtros "zip" e "map" que estavam faltando
app.jinja_env.filters['zip'] = zip
app.jinja_env.add_extension('jinja2.ext.do')  # opcional, mas ajuda em outras coisas

@app.template_filter('data_br')
def data_br(date):
    return date.strftime('%d/%m/%Y') if date else ''

# Banco de dados
db = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='automax'
)
cursor = db.cursor(dictionary=True)

# Flask-Login
login_manager = LoginManager(app)
login_manager.login_view = 'login'

class User(UserMixin):
    def __init__(self, id_usuario, nome, perfil):
        self.id = id_usuario          # Flask-Login usa esse
        self.id_usuario = id_usuario  # Para usar no banco
        self.nome = nome
        self.perfil = perfil

@login_manager.user_loader
def load_user(user_id):
    cursor.execute("SELECT id_usuario, nome, perfil FROM usuario WHERE id_usuario = %s", (user_id,))
    data = cursor.fetchone()
    if data:
        return User(data['id_usuario'], data['nome'], data['perfil'])
    return None

# ====================== ROTAS GERAIS ======================
@app.route('/')
def index():
    return redirect(url_for('login'))

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        login_digitado = request.form['login']
        senha = request.form['senha']
        cursor.execute("SELECT * FROM usuario WHERE login = %s", (login_digitado,))
        user = cursor.fetchone()
        if user and user['senha'] == senha:
            u = User(user['id_usuario'], user['nome'], user['perfil'])
            login_user(u)
            return redirect(url_for('dashboard'))
        flash('Login ou senha incorretos!', 'danger')
    return render_template('login.html')

@app.route('/logout')
@login_required
def logout():
    logout_user()
    flash('Logout realizado com sucesso!')
    return redirect(url_for('login'))

@app.route('/dashboard')
@login_required
def dashboard():
    cursor.execute("SELECT COUNT(*) as total FROM cliente")
    clientes = cursor.fetchone()['total']
    cursor.execute("SELECT COUNT(*) as total FROM ordem_servico WHERE status != 'Finalizada'")
    os_abertas = cursor.fetchone()['total']
    cursor.execute("SELECT COUNT(*) as total FROM peca WHERE estoque_atual <= estoque_minimo")
    alertas = cursor.fetchone()['total']
    return render_template('index.html', user=current_user, clientes=clientes, os_abertas=os_abertas, alertas_estoque=alertas)

# ====================== CRUD CLIENTES ======================
@app.route('/clientes')
@login_required
def clientes():
    cursor.execute("SELECT * FROM cliente ORDER BY nome")
    lista = cursor.fetchall()
    return render_template('clientes.html', clientes=lista, user=current_user)

@app.route('/cliente/novo', methods=['GET', 'POST'])
@login_required
def novo_cliente():
    if request.method == 'POST':
        nome = request.form['nome']
        cpf = request.form['cpf'].replace('.', '').replace('-', '')  # ← LIMPA
        celular = request.form['celular'].replace('(', '').replace(')', '').replace(' ', '').replace('-', '')  # ← LIMPA
        email = request.form['email']

        try:
            cursor.execute("INSERT INTO cliente (nome, cpf, celular, email) VALUES (%s, %s, %s, %s)",
                           (nome, cpf, celular, email))
            db.commit()
            flash('Cliente cadastrado!', 'success')
            return redirect(url_for('clientes'))
        except mysql.connector.IntegrityError:
            flash('Erro: CPF já cadastrado!', 'danger')
    return render_template('cliente_form.html', acao='Novo Cliente', user=current_user)

@app.route('/cliente/editar/<int:id>', methods=['GET', 'POST'])
@login_required
def editar_cliente(id):
    cursor.execute("SELECT * FROM cliente WHERE id_cliente = %s", (id,))
    cliente = cursor.fetchone()
    if not cliente:
        flash('Cliente não encontrado!', 'danger')
        return redirect(url_for('clientes'))

    if request.method == 'POST':
        nome = request.form['nome']
        cpf = request.form['cpf'].replace('.', '').replace('-', '')  # ← LIMPA
        celular = request.form['celular'].replace('(', '').replace(')', '').replace(' ', '').replace('-', '')  # ← LIMPA
        email = request.form['email']

        cursor.execute("UPDATE cliente SET nome=%s, cpf=%s, celular=%s, email=%s WHERE id_cliente=%s",
                       (nome, cpf, celular, email, id))
        db.commit()
        flash('Cliente atualizado!', 'success')
        return redirect(url_for('clientes'))

    return render_template('cliente_form.html', acao='Editar Cliente', cliente=cliente, user=current_user)

@app.route('/cliente/excluir/<int:id>', methods=['POST'])
@login_required
def excluir_cliente(id):
    if current_user.perfil != 'Gerente':
        flash('Apenas gerentes podem excluir clientes!', 'danger')
        return redirect(url_for('clientes'))

    # Verifica se o cliente tem veículos ou OS vinculados
    cursor.execute("SELECT COUNT(*) as total FROM veiculo WHERE id_cliente = %s", (id,))
    veiculos = cursor.fetchone()['total']

    cursor.execute("SELECT COUNT(*) as total FROM ordem_servico WHERE id_cliente = %s", (id,))
    ordens = cursor.fetchone()['total']

    if veiculos > 0 or ordens > 0:
        flash('Não é possível excluir: este cliente possui veículos ou ordens de serviço vinculados!', 'danger')
        return redirect(url_for('clientes'))

    # Se chegou até aqui, pode excluir
    cursor.execute("DELETE FROM cliente WHERE id_cliente = %s", (id,))
    db.commit()
    flash('Cliente excluído com sucesso!', 'success')
    return redirect(url_for('clientes'))

# ====================== CRUD VEÍCULOS ======================
@app.route('/veiculos')
@login_required
def veiculos():
    cursor.execute("""
        SELECT v.*, c.nome as cliente_nome 
        FROM veiculo v 
        JOIN cliente c ON v.id_cliente = c.id_cliente 
        ORDER BY v.placa
    """)
    veiculos_list = cursor.fetchall()
    return render_template('veiculos.html', veiculos=veiculos_list, user=current_user)

@app.route('/veiculo/novo', methods=['GET', 'POST'])
@login_required
def novo_veiculo():
    if request.method == 'POST':
        placa = request.form['placa'].upper()
        modelo = request.form['modelo']
        marca = request.form['marca']
        ano = request.form['ano']
        cor = request.form['cor']
        km_atual = request.form['km_atual']
        id_cliente = request.form['id_cliente']
        try:
            cursor.execute("""INSERT INTO veiculo 
                (placa, modelo, marca, ano, cor, km_atual, id_cliente) 
                VALUES (%s, %s, %s, %s, %s, %s, %s)""",
                (placa, modelo, marca, ano, cor, km_atual, id_cliente))
            db.commit()
            flash('Veículo cadastrado!', 'success')
            return redirect(url_for('veiculos'))
        except mysql.connector.IntegrityError:
            flash('Erro: Placa já cadastrada!', 'danger')

    cursor.execute("SELECT id_cliente, nome FROM cliente ORDER BY nome")
    clientes = cursor.fetchall()
    return render_template('veiculo_form.html', acao='Novo Veículo', clientes=clientes, user=current_user)

@app.route('/veiculo/editar/<int:id>', methods=['GET', 'POST'])
@login_required
def editar_veiculo(id):
    cursor.execute("SELECT * FROM veiculo WHERE id_veiculo = %s", (id,))
    veiculo = cursor.fetchone()
    if not veiculo:
        flash('Veículo não encontrado!', 'danger')
        return redirect(url_for('veiculos'))

    if request.method == 'POST':
        placa = request.form['placa'].upper()
        modelo = request.form['modelo']
        marca = request.form['marca']
        ano = request.form['ano']
        cor = request.form['cor']
        km_atual = request.form['km_atual']
        id_cliente = request.form['id_cliente']
        try:
            cursor.execute("""UPDATE veiculo SET placa=%s, modelo=%s, marca=%s, ano=%s, 
                cor=%s, km_atual=%s, id_cliente=%s WHERE id_veiculo=%s""",
                (placa, modelo, marca, ano, cor, km_atual, id_cliente, id))
            db.commit()
            flash('Veículo atualizado!', 'success')
            return redirect(url_for('veiculos'))
        except mysql.connector.IntegrityError:
            flash('Erro: Placa já está em uso!', 'danger')

    cursor.execute("SELECT id_cliente, nome FROM cliente ORDER BY nome")
    clientes = cursor.fetchall()
    return render_template('veiculo_form.html', acao='Editar Veículo', veiculo=veiculo, clientes=clientes, user=current_user)

@app.route('/veiculo/excluir/<int:id>', methods=['POST'])
@login_required
def excluir_veiculo(id):
    cursor.execute("DELETE FROM veiculo WHERE id_veiculo = %s", (id,))
    db.commit()
    flash('Veículo excluído!', 'success')
    return redirect(url_for('veiculos'))

# ====================== CRUD PEÇAS ======================
@app.route('/pecas')
@login_required
def pecas():
    try:
        cursor.execute("""
            SELECT id_peca, codigo, descricao, preco_custo, preco_venda, 
                   estoque_atual, estoque_minimo, fornecedor
            FROM peca 
            ORDER BY descricao
        """)
        pecas_list = cursor.fetchall()
    except:
        pecas_list = []
    
    return render_template('pecas.html', pecas=pecas_list, user=current_user)

@app.route('/peca/novo', methods=['GET', 'POST'])
@login_required
def novo_peca():
    if request.method == 'POST':
        codigo = request.form['codigo']
        descricao = request.form['descricao']
        preco_custo = float(request.form['preco_custo'] or 0)
        preco_venda = float(request.form['preco_venda'] or 0)
        estoque_atual = int(request.form['estoque_atual'] or 0)
        estoque_minimo = int(request.form['estoque_minimo'] or 0)
        fornecedor = request.form['fornecedor']

        try:
            cursor.execute("""INSERT INTO peca 
                (codigo, descricao, preco_custo, preco_venda, estoque_atual, estoque_minimo, fornecedor)
                VALUES (%s, %s, %s, %s, %s, %s, %s)""",
                (codigo, descricao, preco_custo, preco_venda, estoque_atual, estoque_minimo, fornecedor))
            db.commit()
            flash('Peça cadastrada com sucesso!', 'success')
            return redirect(url_for('pecas'))
        except mysql.connector.IntegrityError:
            flash('Erro: Código já cadastrado!', 'danger')

    # QUANDO É NOVA PEÇA → ENVIA peca=None PARA NÃO DAR ERRO NO TEMPLATE
    return render_template('peca_form.html', acao='Nova Peça', peca=None)


@app.route('/peca/editar/<int:id>', methods=['GET', 'POST'])
@login_required
def editar_peca(id):
    cursor.execute("SELECT * FROM peca WHERE id_peca = %s", (id,))
    peca = cursor.fetchone()

    if not peca:
        flash('Peça não encontrada!', 'danger')
        return redirect(url_for('pecas'))

    if request.method == 'POST':
        codigo       = request.form['codigo']
        descricao    = request.form['descricao']
        preco_custo  = float(request.form['preco_custo'] or 0)
        preco_venda  = float(request.form['preco_venda'] or 0)
        estoque_atual = int(request.form['estoque_atual'])      # ← ESSA LINHA ERA O PROBLEMA
        estoque_minimo = int(request.form['estoque_minimo'])
        fornecedor   = request.form['fornecedor']

        try:
            cursor.execute("""
                UPDATE peca SET
                    codigo = %s,
                    descricao = %s,
                    preco_custo = %s,
                    preco_venda = %s,
                    estoque_atual = %s,
                    estoque_minimo = %s,
                    fornecedor = %s
                WHERE id_peca = %s
            """, (codigo, descricao, preco_custo, preco_venda,
                  estoque_atual, estoque_minimo, fornecedor, id))

            db.commit()
            flash('Peça atualizada com sucesso!', 'success')
            return redirect(url_for('pecas'))

        except Exception as e:
            db.rollback()
            flash(f'Erro ao salvar peça: {e}', 'danger')

    return render_template('peca_form.html', acao='Editar Peça', peca=peca)

@app.route('/peca/excluir/<int:id>', methods=['POST'])
@login_required
def excluir_peca(id):
    # Só gerente pode excluir
    if current_user.perfil != 'Gerente':
        flash('Apenas gerentes podem excluir peças!', 'danger')
        return redirect(url_for('pecas'))

    try:
        cursor.execute("DELETE FROM peca WHERE id_peca = %s", (id,))
        db.commit()
        flash('Peça excluída com sucesso!', 'success')
    except mysql.connector.IntegrityError:
        flash('Não é possível excluir: esta peça já foi usada em uma ordem de serviço.', 'danger')
    
    return redirect(url_for('pecas'))

# ====================== ORDENS DE SERVIÇO ======================
@app.route('/ordens')
@login_required
def ordens():
    cursor.execute("""
        SELECT os.*, c.nome as cliente_nome, v.placa 
        FROM ordem_servico os 
        JOIN cliente c ON os.id_cliente = c.id_cliente 
        JOIN veiculo v ON os.id_veiculo = v.id_veiculo 
        ORDER BY os.data_abertura DESC
    """)
    lista = cursor.fetchall()
    return render_template('ordens.html', ordens=lista, user=current_user)

@app.route('/ordem/nova', methods=['GET', 'POST'])
@login_required
def nova_ordem():
    if request.method == 'POST':
        diagnostico = request.form['diagnostico']
        prazo = request.form['prazo_previsto']
        id_cliente = request.form['id_cliente']
        id_veiculo = request.form['id_veiculo']
        
        cursor.execute("""INSERT INTO ordem_servico 
            (diagnostico, prazo_previsto, id_cliente, id_veiculo, id_usuario_abertura, status)
            VALUES (%s, %s, %s, %s, %s, 'Aberta')""",
            (diagnostico, prazo, id_cliente, id_veiculo, current_user.id))
        db.commit()
        flash('Ordem de serviço criada com sucesso!', 'success')
        return redirect(url_for('ordens'))

    # Carrega clientes
    cursor.execute("SELECT id_cliente, nome FROM cliente ORDER BY nome")
    clientes = cursor.fetchall()

    # Carrega TODOS os veículos com o cliente
    cursor.execute("""
        SELECT v.id_veiculo, v.placa, v.modelo, v.marca, v.id_cliente, c.nome as cliente_nome
        FROM veiculo v
        JOIN cliente c ON v.id_cliente = c.id_cliente
        ORDER BY c.nome, v.placa
    """)
    veiculos = cursor.fetchall()

    return render_template('ordem_form.html', 
                         clientes=clientes, 
                         veiculos=veiculos, 
                         user=current_user)

@app.route('/ordem/<int:id_os>')
@login_required
def ver_ordem(id_os):
    cursor.execute("SELECT os.*, c.nome as cliente_nome, v.placa FROM ordem_servico os JOIN cliente c ON os.id_cliente=c.id_cliente JOIN veiculo v ON os.id_veiculo=v.id_veiculo WHERE id_os=%s", (id_os,))
    ordem = cursor.fetchone()
    if not ordem:
        flash('OS não encontrada!', 'danger')
        return redirect(url_for('ordens'))

    cursor.execute("SELECT i.*, p.descricao, p.preco_venda FROM item_os i JOIN peca p ON i.id_peca=p.id_peca WHERE i.id_os=%s", (id_os,))
    itens = cursor.fetchall()
    cursor.execute("SELECT id_peca, codigo, descricao FROM peca WHERE estoque_atual > 0 ORDER BY descricao")
    pecas = cursor.fetchall()
    return render_template('ordem_detalhe.html', ordem=ordem, itens=itens, pecas=pecas, user=current_user)

@app.route('/ordem/<int:id_os>/adicionar_peca', methods=['POST'])
@login_required
def adicionar_peca_os(id_os):
    id_peca = request.form['id_peca']
    quantidade = int(request.form['quantidade'])
    cursor.execute("INSERT INTO item_os (id_os, id_peca, quantidade) VALUES (%s, %s, %s)", (id_os, id_peca, quantidade))
    cursor.execute("UPDATE peca SET estoque_atual = estoque_atual - %s WHERE id_peca = %s", (quantidade, id_peca))
    db.commit()
    flash('Peça adicionada à OS!', 'success')
    return redirect(url_for('ver_ordem', id_os=id_os))

@app.route('/ordem/<int:id_os>/fechar', methods=['POST'])
@login_required
def fechar_ordem(id_os):
    valor_total = request.form.get('valor_total', 0)
    
    try:
        valor_total = float(valor_total)
        
        # Primeiro calcula o total dos itens (opcional, mas deixa mais seguro)
        cursor.execute("""
            SELECT COALESCE(SUM(i.quantidade * p.preco_venda), 0) as total
            FROM item_os i
            JOIN peca p ON i.id_peca = p.id_peca
            WHERE i.id_os = %s
        """, (id_os,))
        total_calculado = cursor.fetchone()['total']

        # Usa o valor digitado ou o calculado automaticamente
        valor_final = valor_total if valor_total > 0 else total_calculado

        cursor.execute("""
            UPDATE ordem_servico 
            SET status = 'Finalizada',
                data_fechamento = CURDATE(),
                valor_total = %s
            WHERE id_os = %s AND status != 'Finalizada'
        """, (valor_final, id_os))
        
        if cursor.rowcount == 0:
            flash('Esta OS já foi finalizada ou não existe!', 'warning')
        else:
            db.commit()
            flash(f'OS finalizada com sucesso! Valor: R$ {valor_final:.2f}', 'success')
            
    except ValueError:
        flash('Valor total inválido!', 'danger')
    except Exception as e:
        db.rollback()
        flash(f'Erro inesperado: {e}', 'danger')

    return redirect(url_for('ordens'))

# ====================== RELATÓRIOS ======================
@app.route('/relatorios')
@login_required
def relatorios():
    if current_user.perfil != 'Gerente':
        flash('Acesso negado!', 'danger')
        return redirect(url_for('dashboard'))

    cursor.execute("SELECT COALESCE(SUM(valor_total),0) as total FROM ordem_servico WHERE status='Finalizada' AND YEAR(data_fechamento)=YEAR(CURDATE()) AND MONTH(data_fechamento)=MONTH(CURDATE())")
    faturamento = cursor.fetchone()['total']
    cursor.execute("SELECT COUNT(*) as total FROM ordem_servico WHERE status='Finalizada'")
    total_os = cursor.fetchone()['total']

    return render_template('relatorios.html', faturamento=faturamento, total_os=total_os, user=current_user)

if __name__ == '__main__':
    app.run(debug=True, port=5000)