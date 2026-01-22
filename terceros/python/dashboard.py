# dashboard con 6 tipos de graficos, filtros y mejorado

import dash
from dash import Dash, html, dcc, Input, Output, State, callback
import plotly.express as px
import pandas as pd
from urllib.parse import parse_qs, unquote
import json

app = Dash(__name__)

# Configuración de gráficos disponibles
GRAFICOS_DISPONIBLES = [
    {'label': 'Barras Horizontales', 'value': 'barh'},
    {'label': 'Barras Verticales', 'value': 'bar'},
    {'label': 'Gráfico de Pastel', 'value': 'pie'},
    {'label': 'Gráfico de Líneas', 'value': 'line'},
    {'label': 'Gráfico de Dispersión', 'value': 'scatter'},
    {'label': 'Gráfico de Área', 'value': 'area'},
    {'label': 'Gráfico Sunburst', 'value': 'sunburst'}
]

app.layout = html.Div([
    html.H1("Dashboard de Terceros por Municipio", style={'textAlign': 'center', 'color': '#2c3e50', 'marginBottom': '20px'}),
    dcc.Location(id='url', refresh=False),
    
    # Contenedor de filtros
    html.Div([
        # Filtro por cantidad mínima
        html.Div([
            html.Label("Cantidad mínima:", className='filter-label'),
            dcc.Input(
                id='filtro-cantidad',
                type='number',
                min=0,
                value=0,
                style={'width': '100px'},
                className='filter-input'
            )
        ], className='filter-control'),
        
        # Filtro por texto
        html.Div([
            html.Label("Buscar municipio:", className='filter-label'),
            dcc.Input(
                id='filtro-texto',
                type='text',
                placeholder='Ej: MED',
                style={'width': '100px'},
                className='filter-input'
            )
        ], className='filter-control'),
        
        # Filtro por dropdown
        html.Div([
            html.Label("Seleccionar:", className='filter-label'),
            dcc.Dropdown(
                id='filtro-municipios-dropdown',
                options=[],
                multi=True,
                placeholder="Todos...",
                style={'width': '250px'},
                className='filter-dropdown'
            )
        ], className='filter-control'),
        
        # Selector de tipo de gráfico
        html.Div([
            html.Label("Tipo de gráfico:", className='filter-label'),
            dcc.Dropdown(
                id='selector-grafico',
                options=GRAFICOS_DISPONIBLES,
                value='barh',
                clearable=False,
                style={'width': '200px'},
                className='graph-type-dropdown'
            )
        ], className='filter-control'),
        
        html.Button('Aplicar Filtros', id='btn-filtrar', n_clicks=0, className='apply-button')
    ], className='filters-container'),
    
    # Resultados
    html.Div(id='titulo-municipios', className='results-title'),
    dcc.Graph(id='grafico-municipios', className='main-graph'),
    html.Div(id='total-terceros', className='total-container')
])

# Con esta nueva sintaxis:
app._favicon = None  # Opcional: para evitar warning de favicon

# CSS externo
app.index_string = '''
<!DOCTYPE html>
<html>
    <head>
        {%metas%}
        <title>{%title%}</title>
        {%favicon%}
        {%css%}
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <style>
            .filters-container {
                margin: 20px;
                padding: 20px;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                background: #f9f9f9;
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                align-items: center;
            }
            
            .filter-control {
                display: flex;
                flex-direction: column;
                min-width: 150px;
            }
            
            .filter-label {
                margin-bottom: 5px;
                font-family: 'Roboto', sans-serif;
                font-weight: 500;
                color: #555;
            }
            
            .filter-input {
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            
            .filter-dropdown, .graph-type-dropdown {
                width: 100%;
            }
            
            .apply-button {
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-family: 'Roboto', sans-serif;
                font-weight: 500;
                align-self: flex-end;
                margin-left: auto;
            }
            
            .apply-button:hover {
                background-color: #45a049;
            }
            
            .results-title {
                margin: 20px;
                font-family: 'Roboto', sans-serif;
                font-size: 1.2em;
                color: #333;
            }
            
            .main-graph {
                margin: 20px;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .total-container {
                margin: 20px;
                padding: 15px;
                background-color: #f0f8ff;
                border-radius: 8px;
                font-family: 'Roboto', sans-serif;
            }
        </style>
    </head>
    <body>
        {%app_entry%}
        <footer>
            {%config%}
            {%scripts%}
            {%renderer%}
        </footer>
    </body>
</html>
'''

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
                height=500 + len(df)*15,
                text='cantidad'
            )
            fig.update_traces(textposition='outside')
            fig.update_layout(showlegend=False)
            
        elif tipo_grafico == 'bar':
            fig = px.bar(
                df,
                x='municipio',
                y='cantidad',
                color='municipio',
                labels={'cantidad': 'Número de Terceros', 'municipio': ''},
                text='cantidad'
            )
            fig.update_traces(textposition='outside')
            
        elif tipo_grafico == 'pie':
            fig = px.pie(
                df,
                names='municipio',
                values='cantidad',
                title='Distribución por Municipio',
                hole=0.3
            )
            
        elif tipo_grafico == 'line':
            fig = px.line(
                df,
                x='municipio',
                y='cantidad',
                markers=True,
                labels={'cantidad': 'Número de Terceros', 'municipio': ''},
                text='cantidad'
            )
            fig.update_traces(textposition='top center')
            
        elif tipo_grafico == 'scatter':
            fig = px.scatter(
                df,
                x='municipio',
                y='cantidad',
                size='cantidad',
                color='municipio',
                labels={'cantidad': 'Número de Terceros', 'municipio': ''},
                hover_name='municipio',
                size_max=30
            )
            
        elif tipo_grafico == 'area':
            fig = px.area(
                df,
                x='municipio',
                y='cantidad',
                labels={'cantidad': 'Número de Terceros', 'municipio': ''},
                line_shape='spline'
            )
            
        elif tipo_grafico == 'sunburst':
            # Para sunburst necesitamos una jerarquía, aquí usamos municipio como único nivel
            fig = px.sunburst(
                df,
                path=['municipio'],
                values='cantidad',
                title='Distribución Jerárquica'
            )
        
        titulo = html.H4([
            html.Span("Visualizando ", style={'color': '#555'}),
            html.Span(f"{len(df)} municipios", style={'color': '#007BFF', 'fontWeight': 'bold'}),
            html.Span(" | Total terceros: ", style={'color': '#555'}),
            html.Span(f"{total}", style={'color': '#4CAF50', 'fontWeight': 'bold'})
        ])
        
        total_texto = html.Div([
            html.P("Detalles:", style={'fontWeight': 'bold'}),
            html.Ul([
                html.Li(f"Municipio con más terceros: {df.iloc[0]['municipio']} ({df.iloc[0]['cantidad']})"),
                html.Li(f"Municipio con menos terceros: {df.iloc[-1]['municipio']} ({df.iloc[-1]['cantidad']})")
            ] if len(df) > 0 else [html.P("No hay datos para mostrar")])
        ])
        
    except Exception as e:
        titulo = html.H4("Error al procesar datos", style={'color': 'red'})
        fig = px.bar(title=f"Error: {str(e)}")
        total_texto = html.P("Por favor verifique los filtros aplicados", style={'color': '#777'})
    
    return titulo, fig, total_texto

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=8050)