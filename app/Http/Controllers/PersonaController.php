<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Persona;
use App\Models\TipoPersona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipo=TipoPersona::all();
        return response()->json(["tipo"=>$tipo]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Reglas de validación
        $rules = [
            'nombre' => 'required', // Valida que el nombre sea requerido y sea una cadena
            'apellido' => 'required', // Valida que el apellido sea requerido y sea una cadena
            'cedula' => 'required', // Valida que la cédula sea única y requerida
            'correo' => 'required|email|unique:personas,correo', // Verifica que el correo sea único en la tabla personas
            'telefono' => 'required|string|unique:personas,telefono', // Verifica que el teléfono sea único
            'especialidad' => 'nullable', // La especialidad es opcional
            'tipoPersona_id' => 'required|integer|exists:tipo_personas,id', // Verifica que el tipoPersona_id exista en la tabla tipo_personas
        ];
    
        // Mensajes de error personalizados
        $customMessages = [
            'required' => 'El campo :attribute es obligatorio.',
            'unique' => 'El :attribute ya está registrado.',
            'exists' => 'El :attribute seleccionado no es válido.',
            // Puedes personalizar más mensajes aquí
        ];
    
        // Realiza la validación
        $validator = Validator::make($request->all(), $rules, $customMessages);
    
        if ($validator->fails()) {
            // Si la validación falla, retorna una respuesta con los errores
            return response()->json($validator->errors(), 422);
        }
    
        // Si la validación es exitosa, procede a crear la persona
        $persona = Persona::create($request->all());
    
        // Retorna una respuesta exitosa con los datos de la persona creada
        return response()->json(['persona' => $persona], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Persona $persona)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function buscarMedicosPorEspecialidad(Request $request)
{
    // Validar que el parámetro de búsqueda 'especialidad' esté presente en la solicitud
    $request->validate([
        'especialidad' => 'required|string',
    ]);

    // Obtener el tipoPersona_id para médicos
    $tipoPersonaMedico = 2; // Asumiendo que 2 es el ID para médicos

    // Buscar en la base de datos por médicos con la especialidad proporcionada
    $medicos = Persona::where('tipoPersona_id', $tipoPersonaMedico)
                      ->where('especialidad', 'LIKE', '%' . $request->especialidad . '%')
                      ->get();

    // Retornar los médicos encontrados en formato JSON
    return response()->json(['medicos' => $medicos]);
}
    /**
     * Update the specified resource in storage.
     */
    public function mostrarCitasReservadas()
    {
        // Realizar una consulta para obtener las citas reservadas con nombres de médicos y pacientes, y la fecha de la cita
        $citas = DB::table('citas')
                    ->join('personas as medico', 'citas.medico', '=', 'medico.id')
                    ->join('personas as paciente', 'citas.persona_id', '=', 'paciente.id')
                    ->select('medico.nombre as nombre_medico', 'paciente.nombre as nombre_paciente', 'citas.fechaYhora as fecha_cita')
                    ->get();
    
        // Retornar las citas en formato JSON
        return response()->json(['citas' => $citas]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function reservarCita(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'nombre_paciente' => 'required', // Requerir el nombre del paciente
            'medico_id' => 'required|exists:personas,id', // Verificar que el médico exista en la tabla personas
            'fecha_hora' => 'required', // Verificar que la fecha y hora estén en el formato correcto
        ]);
    
        // Buscar al paciente por su nombre
        $paciente = Persona::where('nombre', $request->nombre_paciente)->first();
    
        // Verificar si se encontró al paciente
        if (!$paciente) {
            return response()->json(['error' => 'El paciente especificado no existe.'], 422);
        }
    
        // Verificar si el médico tiene alguna cita programada para la fecha y hora especificadas
        $citaExistente = Cita::where('medico', $request->medico_id)
                             ->where('fechaYhora', $request->fecha_hora)
                             ->exists();
    
        // Si el médico ya tiene una cita programada para esa fecha y hora, devuelve un error
        if ($citaExistente) {
            return response()->json(['error' => 'El médico ya tiene una cita programada para esta fecha y hora.'], 422);
        }
    
        // Crear la cita en la base de datos
        $cita = Cita::create([
            'medico' => $request->medico_id,
            'fechaYhora' => $request->fecha_hora,
            'persona_id' => $paciente->id, // Utilizar el ID del paciente encontrado
        ]);
    
        // Retornar una respuesta exitosa con el nombre del paciente
        return response()->json(['mensaje' => 'Cita reservada exitosamente.', 'cita' => $cita, 'paciente_nombre' => $paciente->nombre]);
    }
}
