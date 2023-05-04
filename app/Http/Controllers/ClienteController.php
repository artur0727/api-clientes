<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use DateTime;
use DateInterval;

class ClienteController extends Controller
{
    /** 
     * Crea un nuevo Cliente
     * @return Json
    **/
    public function crearCliente(Request $request)
    {
        
        // Definir reglas de validación
        $rules = [
            'nombre' => 'required|regex:/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/',
            'apellido' => 'required|regex:/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/',
            'edad' => 'required|integer|min:0',
            'fecha_nacimiento' => 'required|date_format:d-m-Y|before_or_equal:today',//date_format:Y-m-d
        ];

        // Definir mensajes de error personalizados
        $messages = [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.regex' => 'El campo nombre solo debe contener letras y espacios.',
            'apellido.required' => 'El campo apellido es requerido.',
            'apellido.regex' => 'El campo apellido solo debe contener letras y espacios.',
            'edad.required' => 'El campo edad es requerido.',
            'edad.integer' => 'El campo edad tiene que ser de tipo integer.',
            'edad.min' => 'La edad mínima es de 0.',
            'fecha_nacimiento.required' => 'El campo fecha_nacimiento es requerido.',
            'fecha_nacimiento.date_format' => 'El campo fecha_nacimiento tiene que ser de tipo date (d-m-Y).',
            'fecha_nacimiento.before_or_equal' => 'El campo fecha_nacimiento debe ser una fecha anterior o igual a la de hoy.'
        ];

        // Validar los campos del cliente
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function ($validator) use ($request) {

            // Convertir la primera letra de nombre y apellido a mayúscula
            $request->merge([
                'nombre' => ucfirst(strtolower($request->nombre)),
                'apellido' => ucfirst(strtolower($request->apellido)),
            ]);

            // Verifica si los campos edad y fecha de nacimiento están presentes
            if ($request->has('edad') && $request->has('fecha_nacimiento')) {
                // Agrega reglas de validación personalizada para la edad y la fecha de nacimiento
                $edad = $request->edad;
                $fechaNacimiento = Carbon::createFromFormat('d-m-Y', $request->fecha_nacimiento);
                $hoy = Carbon::now();

                if ($edad !== $fechaNacimiento->age) {
                    $validator->errors()->add('edad', 'La edad no coincide con la fecha de nacimiento.');
                }
            }
        });

        if($validator->fails()){

            return response()->json(['error' => $validator->errors()], 401);     

        }

        $cliente = new Cliente(); //Instancia el objeto Cliente en una variable

        // Obtén los datos de la solicitud
        $cliente->nombre = $request->input('nombre');
        $cliente->apellido = $request->input('apellido');
        $cliente->edad = $request->input('edad');
        $cliente->fecha_nacimiento = $request->input('fecha_nacimiento');

        // Crea/guarda una nueva entrada de cliente
        $cliente->save();

        // Retorna una respuesta adecuada, por ejemplo:
        return response()->json([
            'mensaje' => 'Cliente creado con éxito',
            'cliente_creado' => $cliente
        ], 201);

    }


    /** 
     * Devuelve un json con el promedio de edades y desviación estandar de los clientes registrados.
     * @return Json
    **/
    public function obtenerPromedioYDesviacion()
    {
        //Obtiene todos los clientes
        $clientes = Cliente::all();

        if ($clientes->isEmpty()) {
            return response()->json(['mensaje' => 'No hay clientes registrados'], 404);
        }

        // Cañcula el promedio de edad de los clientes
        $edades = $clientes->pluck('edad')->toArray();
        $promedioEdad = array_sum($edades) / count($edades);

        // Calcula la desviación estándar de edad
        $sumaCuadrados = array_sum(array_map(fn ($edad) => pow($edad - $promedioEdad, 2), $edades));
        $desviacionEstandar = sqrt($sumaCuadrados / count($edades));

        return response()->json([
            'promedio_edad' => round($promedioEdad, 2),
            'desviacion_estandar' => round($desviacionEstandar, 2)
        ], 200);

    }


    /** 
     * Listado de todos los registros de clientes + fecha probable de muerte.
     * @return Json
    **/
    public function listarClientes()
    {

        $clientes = Cliente::all();

        if ($clientes->isEmpty()) {
            return response()->json(['mensaje' => 'No hay clientes registrados'], 404);
        }

        $datos_clientes = [];
        foreach ($clientes as $cliente) {

            $fech_nac = $cliente->fecha_nacimiento;

            $datos_cliente = [
                'nombre' => $cliente->nombre,
                'apellido' => $cliente->apellido,
                'edad' => $cliente->edad,
                'fecha_nacimiento' => $fech_nac->format('d-m-Y'),
                'fecha_probable_muerte' => $this->calcularFechaProbableMuerte($fech_nac->format('d-m-Y'))
            ];
            array_push($datos_clientes, $datos_cliente);
        }

        return response()->json($datos_clientes, 200);

    }

    /**
     * Función que llama el Controlador de listarClientes donde pasa la variable $fecha_nacimiento 
     * para calcular la fecha probable de muerte.
     * 
     * @return String
     */
    private function calcularFechaProbableMuerte($fecha_nacimiento)
    {
        // Aquí puedes implementar la lógica para calcular la fecha probable de muerte de cada cliente
        // Por ejemplo, podrías basarte en la esperanza de vida promedio en tu país
        // Para este ejemplo, se calcula la fecha probable de muerte (asumiendo una expectativa de vida promedio de 80 años)
        $fecha_probable_muerte = new DateTime($fecha_nacimiento);
        $fecha_probable_muerte->add(new DateInterval('P80Y'));
        $fecha_probable_muerte = $fecha_probable_muerte->format('d-m-Y');
        return $fecha_probable_muerte; 
    }
}
