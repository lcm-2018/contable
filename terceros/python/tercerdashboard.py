#dashboard con filtros y recibe datos desde php

import dash
from dash import Dash, html, dcc, Input, Output, State, callback
import plotly.express as px
import pandas as pd
from urllib.parse import parse_qs, unquote
import json

app = Dash(__name__)

app.layout = html.Div([
    html.H1("Distribución de Terceros por Municipio", style={'textAlign': 'center'}),
    dcc.Location(id='url', refresh=False),
    
    # Contenedor de filtros
    html.Div([
        # Filtro por cantidad mínima
        html.Label("Cantidad mínima:", style={'marginRight': '10px'}),
        dcc.Input(
            id='filtro-cantidad',
            type='number',
            min=0,
            value=0, # valor por defecto sin filtro
            style={'width': '80px', 'marginRight': '20px'}
        ),
        
        # Filtro por texto (búsqueda flexible)
        html.Label("Buscar texto:", style={'marginRight': '10px'}),
        dcc.Input(
            id='filtro-texto',
            type='text',
            placeholder='Ej: MED',
            style={'width': '150px', 'marginRight': '20px'}
        ),
        
        # Filtro por dropdown (selección múltiple)
        html.Label("Municipios específicos:", style={'marginRight': '10px'}),
        dcc.Dropdown(
            id='filtro-municipios-dropdown',
            options=[],
            multi=True,
            placeholder="Seleccione...",
            style={'width': '300px', 'marginRight': '20px'}
        ),
        
        html.Button('Aplicar Filtros', id='btn-filtrar', n_clicks=0,
                  style={'backgroundColor': '#007BFF', 'color': 'white'})
    ], style={
        'margin': '20px',
        'padding': '15px',
        'border': '1px solid #ddd',
        'display': 'flex',
        'flexWrap': 'wrap',
        'gap': '10px',
        'alignItems': 'center'
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
        # Ordenamos alfabéticamente
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
     State('filtro-municipios-dropdown', 'value')]
)
def actualizar_grafico(n_clicks, search, cantidad_min, texto_busqueda, municipios_seleccionados):
    params = parse_qs(search.lstrip('?')) if search else {}
    datos_json = params.get('datos', ['[]'])[0]
    
    try:
        datos = json.loads(unquote(datos_json))
        df = pd.DataFrame(datos)
        
        # Aplicamos filtros en cascada
        if cantidad_min and cantidad_min > 0:
            df = df[df['cantidad'] >= cantidad_min]
            
        if texto_busqueda:
            df = df[df['municipio'].str.contains(
                texto_busqueda, case=False, na=False)]
            
        if municipios_seleccionados and len(municipios_seleccionados) > 0:
            df = df[df['municipio'].isin(municipios_seleccionados)]
        
        # Ordenamos por cantidad (descendente)
        df = df.sort_values('cantidad', ascending=False)
        total = df['cantidad'].sum()
        
        # Creamos gráfico
        fig = px.bar(
            df,
            x='cantidad',
            y='municipio',
            orientation='h',
            color='municipio',
            labels={'cantidad': 'Número de Terceros', 'municipio': ''},
            height=500 + len(df)*20  # Ajuste dinámico de altura
        )
        
        fig.update_layout(
            showlegend=False,
            yaxis={'categoryorder': 'total ascending'}
        )
        
        titulo = html.H4(f"Resultados: {len(df)} municipios")
        total_texto = html.Div([
            html.Span("Total terceros: ", style={'fontWeight': 'bold'}),
            html.Span(f"{total}", style={'color': '#007BFF'})
        ])
        
    except Exception as e:
        titulo = html.H4("Error al cargar datos", style={'color': 'red'})
        fig = px.bar(title=f"Error: {str(e)}")
        total_texto = ""
    
    return titulo, fig, total_texto

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=8050)