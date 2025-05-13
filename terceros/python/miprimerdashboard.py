#dashboard basico sin recibir datos de php

import dash
from dash import Dash, html, dcc, Input, Output
from dash.dependencies import Input, Output
import plotly.express as px
import pandas as pd
from urllib.parse import parse_qs

# Inicializar la app
app = Dash(__name__)

# Datos de ejemplo
df = pd.DataFrame({
    "Producto": ["Laptops", "Telefonos", "Tablets", "Monitores", "Teclados", "Mouse"],
    "Ventas": [120, 90, 50, 80, 70, 10]
})

#diseño del dashboard
app.layout = html.Div([
    html.H1("Dashboard de Terceros", style={"textAlign": "center"}),
    dcc.Location(id='url', refresh=False),  # Componente para manejar la URL
    html.Div(id='output-message'),  # Para mostrar el parámetro recibido
    dcc.Graph(id='graph')
])

# Callback que actualiza el gráfico según la URL
@app.callback(
    [Output('output-message', 'children'),
     Output('graph', 'figure')],
    [Input('url', 'search')]
)
def update_graph(search):
    params = parse_qs(search.lstrip('?')) if search else {}
    producto = params.get('producto', ['Todos'])[0]  # Valor por defecto: 'Todos'
    
    filtered_df = df if producto == 'Todos' else df[df['Producto'] == producto]
    
    mensaje = f"Mostrando: {producto}"
    fig = px.bar(filtered_df, x="Producto", y="Ventas", title=f"Ventas de {producto}")
    
    return mensaje, fig

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=8050, use_reloader=False)
