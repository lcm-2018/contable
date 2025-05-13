#dashboard implementando filtros y 4 tipos de graficos

import dash
from dash import Dash, html, dcc, Input, Output, State, callback
import plotly.express as px
import pandas as pd
from urllib.parse import parse_qs, unquote
import json

app = Dash(__name__)

app.layout = html.Div([
    html.H1("Dashboard de Terceros por Municipio", style={'textAlign': 'center', 'color': '#2c3e50'}),
    dcc.Location(id='url', refresh=False),
    
    # Contenedor de filtros
    html.Div([
        # Filtro por cantidad mínima
        html.Div([
            html.Label("Cantidad mínima:", style={'marginRight': '10px'}),
            dcc.Input(
                id='filtro-cantidad',
                type='number',
                min=0,
                value=0,
                style={'width': '80px'}
            )
        ], style={'marginRight': '20px', 'display': 'inline-block'}),
        
        # Filtro por texto
        html.Div([
            html.Label("Buscar municipio:", style={'marginRight': '10px'}),
            dcc.Input(
                id='filtro-texto',
                type='text',
                placeholder='Ej: MED',
                style={'width': '150px'}
            )
        ], style={'marginRight': '20px', 'display': 'inline-block'}),
        
        # Filtro por dropdown
        html.Div([
            html.Label("Seleccionar:", style={'marginRight': '10px'}),
            dcc.Dropdown(
                id='filtro-municipios-dropdown',
                options=[],
                multi=True,
                placeholder="Todos...",
                style={'width': '200px'}
            )
        ], style={'marginRight': '20px', 'display': 'inline-block'}),
        
        # Selector de tipo de gráfico
        html.Div([
            html.Label("Tipo de gráfico:", style={'marginRight': '10px'}),
            dcc.Dropdown(
                id='selector-grafico',
                options=[
                    {'label': 'Barras Horizontales', 'value': 'barh'},
                    {'label': 'Barras Verticales', 'value': 'bar'},
                    {'label': 'Gráfico de Pastel', 'value': 'pie'},
                    {'label': 'Gráfico de Líneas', 'value': 'line'}
                ],
                value='barh',
                clearable=False,
                style={'width': '180px'}
            )
        ], style={'marginRight': '20px', 'display': 'inline-block'}),
        
        html.Button('Aplicar Filtros', id='btn-filtrar', n_clicks=0,
                  style={'backgroundColor': '#007BFF', 'color': 'white'})
    ], style={
        'margin': '20px',
        'padding': '15px',
        'border': '1px solid #ddd',
        'borderRadius': '5px'
    }),
    
    # Resultados
    html.Div(id='titulo-municipios', style={'margin': '20px'}),
    dcc.Graph(id='grafico-municipios'),
    html.Div(id='total-terceros', style={'margin': '20px'})
])

# Callback para cargar opciones del dropdown
@callback(
    Output('filtro-municipios-dropdown', 'options'),
    Input('url', 'search')
)
def cargar_opciones_municipios(search):
    params = parse_qs(search.lstrip('?')) if search else {}
    datos_json = params.get('datos', ['[]'])[0]
    
    try:
        datos = json.loads(unquote(datos_json))
        df = pd.DataFrame(datos)
        municipios = sorted(df['municipio'].unique())
        return [{'label': m, 'value': m} for m in municipios]
    except:
        return []

# Callback principal
@callback(
    [Output('titulo-municipios', 'children'),
     Output('grafico-municipios', 'figure'),
     Output('total-terceros', 'children')],
    [Input('btn-filtrar', 'n_clicks')],
    [State('url', 'search'),
     State('filtro-cantidad', 'value'),
     State('filtro-texto', 'value'),
     State('filtro-municipios-dropdown', 'value'),
     State('selector-grafico', 'value')]
)
def actualizar_grafico(n_clicks, search, cantidad_min, texto_busqueda, municipios_seleccionados, tipo_grafico):
    params = parse_qs(search.lstrip('?')) if search else {}
    datos_json = params.get('datos', ['[]'])[0]
    
    try:
        datos = json.loads(unquote(datos_json))
        df = pd.DataFrame(datos)
        
        # Aplicar filtros
        if cantidad_min and cantidad_min > 0:
            df = df[df['cantidad'] >= cantidad_min]
            
        if texto_busqueda:
            df = df[df['municipio'].str.contains(texto_busqueda, case=False, na=False)]
            
        if municipios_seleccionados and len(municipios_seleccionados) > 0:
            df = df[df['municipio'].isin(municipios_seleccionados)]
        
        # Ordenar
        df = df.sort_values('cantidad', ascending=False)
        total = df['cantidad'].sum()
        
        # Crear gráfico según tipo seleccionado
        if tipo_grafico == 'barh':
            fig = px.bar(
                df,
                x='cantidad',
                y='municipio',
                orientation='h',
                color='municipio',
                labels={'cantidad': 'Número de Terceros', 'municipio': ''},
                height=500 + len(df)*15
            )
            fig.update_layout(showlegend=False)
            
        elif tipo_grafico == 'bar':
            fig = px.bar(
                df,
                x='municipio',
                y='cantidad',
                color='municipio',
                labels={'cantidad': 'Número de Terceros', 'municipio': ''}
            )
            
        elif tipo_grafico == 'pie':
            fig = px.pie(
                df,
                names='municipio',
                values='cantidad',
                title='Distribución por Municipio'
            )
            
        elif tipo_grafico == 'line':
            fig = px.line(
                df,
                x='municipio',
                y='cantidad',
                markers=True,
                labels={'cantidad': 'Número de Terceros', 'municipio': ''}
            )
        
        titulo = html.H4(f"Visualizando {len(df)} municipios | {total} terceros totales")
        total_texto = ""
        
    except Exception as e:
        titulo = html.H4("Error al procesar datos", style={'color': 'red'})
        fig = px.bar(title=f"Error: {str(e)}")
        total_texto = ""
    
    return titulo, fig, total_texto

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=8050)