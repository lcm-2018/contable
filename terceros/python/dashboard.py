import dash
from dash import Dash, html, dcc, Input, Output
import plotly.express as px
import pandas as pd
from urllib.parse import parse_qs, unquote
import json

app = Dash(__name__)

app.layout = html.Div([
    html.H1("Distribución de Terceros por Municipio", style={'textAlign': 'center', 'color': '#2c3e50'}),
    dcc.Location(id='url', refresh=False),
    html.Div(id='titulo-municipios', style={'margin': '20px', 'fontSize': '18px'}),
    dcc.Graph(id='grafico-municipios'),
    html.Div(id='total-terceros', style={'margin': '20px', 'fontWeight': 'bold'})
])

@app.callback(
    [Output('titulo-municipios', 'children'),
     Output('grafico-municipios', 'figure'),
     Output('total-terceros', 'children')],
    [Input('url', 'search')]
)
def actualizar_grafico(search):
    params = parse_qs(search.lstrip('?')) if search else {}
    datos_json = params.get('datos', ['[]'])[0]
    
    try:
        datos = json.loads(unquote(datos_json))
        df = pd.DataFrame(datos)
        
        if df.empty:
            raise ValueError("No se recibieron datos válidos")
            
        # Ordenamos por cantidad descendente
        df = df.sort_values('cantidad', ascending=False)
        
        # Calculamos el total
        total = df['cantidad'].sum()
        
        # Creamos gráfico de barras horizontal
        fig = px.bar(df, 
                     x='cantidad', 
                     y='municipio',
                     orientation='h',
                     color='municipio',
                     title="",
                     labels={'cantidad': 'Número de Terceros', 'municipio': 'Municipio'})
        
        fig.update_layout(showlegend=False)
        
        titulo = "Distribución de Terceros por Municipio"
        total_texto = f"Total de terceros registrados: {total}"
        
    except Exception as e:
        titulo = f"Error: {str(e)}"
        fig = px.bar(title="Error al cargar datos")
        total_texto = ""
    
    return titulo, fig, total_texto

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=8050)