<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response; // objeto para enviar la respuesta al usuario
use Psr\Http\Message\ServerRequestInterface as Request; //objeto que contiene toda la informacion de lo que el usuario nos pidio
use Psr\Container\ContainerInterface; //molde oficial para poder recibir nuestra caja de heramientas
use App\Models\Paciente as PacienteModel;
use App\Models\User;
use Illuminate\Database\QueryException;

class Paciente {
    private ContainerInterface $container; 

    public function __construct(ContainerInterface $c) {
        $this->container = $c; 
    }

    public function read(Request $request, Response $response, array $args) {
        $this->container->get('eloquent');

        if (isset($args['id'])) {
            $pacientes = PacienteModel::query()
                ->select(['id', 'cedula','nombre_completo', 'fecha_nacimiento', 'telefono'])
                ->find($args['id']);

                //if($pacientes === null) {
                    //return $this->json($response, ['errors' => $errors], 422);
                //}

            return $this->json($response, ['data' => $pacientes], 200);
        }

        $pacientes = PacienteModel::query()
            ->select(['id', 'cedula', 'nombre_completo', 'fecha_nacimiento', 'telefono'])
            ->get()
            ->toArray();

        return $this->json($response, ['data' => $pacientes], 200);

    }


    public function create(Request $request, Response $response, array $args): Response {
        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];

        $errors = [];
        $idCard = trim((string) ($data['cedula']  ?? ''));
        $fullName = trim((string) ($data['nombre_completo'] ?? ''));
        $dateOfBirth = trim((string) ($data['fecha_nacimiento']  ?? ''));
        $phone = isset($data['telefono']) ? trim((string) $data['telefono']) : null;
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if (preg_match('/^\d{9,12}$/', $idCard) !==1)  { 
            $errors['cedula'] = 'Es obligatorio y debe contener solo dígitos, entre 9 y 12 caracteres.';
        }
        if ($fullName === '' || mb_strlen($fullName) > 150) {
            $errors['nombre_completo'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
        }
        if (\DateTime::createFromFormat('Y-m-d', $dateOfBirth) === false) { 
            $errors['fecha_nacimiento'] = 'Debe ser una fecha válida en formato YYYY-MM-DD.';
        }
        if ($username === '' || mb_strlen($username) > 50) {
            $errors['username'] = 'Es obligatorio y debe tener máximo 50 caracteres.';
        }
        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Debe tener al menos 8 caracteres.';
        }
        if ($phone !== null && $phone === '') {
            $phone = null;
        } elseif ($phone !== null && mb_strlen($phone) > 20) {
            $errors['telefono'] = 'Debe tener máximo 20 caracteres.';
        }

        if ($errors !== []) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        try {
            $eloquent = $this->container->get('eloquent');
            $paciente = $eloquent->connection()->transaction(
                static function () use ($idCard, $fullName, $dateOfBirth, $phone, $username, $password): PacienteModel {
                    $roleId = User::query()
                        ->from('roles')
                        ->where('nombre', 'Paciente')
                        ->value('id');

                    if ($roleId === null) {
                        throw new \RuntimeException('El rol Paciente no está configurado.');
                    }

                    $user = User::create([
                        'username' => $username,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'rol_id' => $roleId,
                        'activo' => true,
                    ]);

                    return PacienteModel::create([
                        'usuario_id' => $user->id,
                        'cedula' => $idCard,
                        'nombre_completo' => $fullName,
                        'fecha_nacimiento' => $dateOfBirth,
                        'telefono' => $phone,
                    ]);
                }
            );
        } catch (QueryException $exception) {

            if (  (int) ($exception->errorInfo[1] ?? 0) === 1062    ) {

                $message = (string) ($exception->errorInfo[2] ?? '');

                if (str_contains($message, 'username')) {
                    return $this->json($response, ['error' => 'El nombre de usuario ya está registrado.'], 409);
                }

                return $this->json($response, ['error' => 'La cedula ya está registrada.'], 409);
            }

            throw $exception;
        }

        return $this->json($response, ['data' => $paciente->toArray()], 201);
    }


public function update(Request $request, Response $response, array $args): Response {
        // Implementation for updating a Medico record would go here.
        $eloquent = $this->container->get('eloquent');
        if (isset($args['id'])) {
            $paciente = PacienteModel::find($args['id']);

            if ($paciente === null) {
                return $this->json($response, ['error' => 'Paciente no encontrado.'], 404);
            }

            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];

            // Validate and update fields as necessary
            // For example:
            if (isset($data['nombre_completo'])) {
                $fullName = trim((string) $data['nombre_completo']);
                
                if ($fullName === '' || mb_strlen($fullName) > 150) {
                    return $this->json($response, ['error' => 'Nombre completo inválido.'], 422);
                }
                $paciente->nombre_completo = $fullName;
            }

            if (isset($data['telefono'])) {
                $phone = trim((string) $data['telefono']);

                if ($phone !== null && mb_strlen($phone) > 20) {
                    $errors['telefono'] = 'Debe tener máximo 20 caracteres.';
                    return $this->json($response, ['error' => 'Telefono invalido.'], 422);
                }
                $paciente->telefono = $phone;
            }

            // Update other fields similarly...

            $paciente->save();

            return $this->json($response, ['data' => $paciente->toArray()], 200);
            }
        return $this->json($response, ['error' => 'Datos incompletos'], 400);
    }


public function delete(Request $request, Response $response, array $args): Response
    {
        $eloquent = $this->container->get('eloquent');
        if (isset($args['id'])) {
            $paciente = PacienteModel::find($args['id']);

            if ($paciente === null) {
                return $this->json($response, ['error' => 'Paciente no encontrado.'], 404);
            }

            $citasCount = $eloquent->table('citas')
                ->where('paciente_id', $paciente->id)
                ->count();

            if ($citasCount > 0) {
                return $this->json($response, ['error' => 'No se puede eliminar al paciente porque tiene citas asignadas'], 409);
            }

            $eloquent->connection()->transaction(static function () use ($paciente): void {
                $usuarioId = $paciente->usuario_id;

                $paciente->delete();

                if ($usuarioId !== null) {
                    $usuario = User::find($usuarioId);
                    if ($usuario !== null) {
                        $usuario->delete();
                    }
                }
            });

            return $this->json($response, ['message' => 'Paciente eliminado exitosamente'], 200);
        }
    return $this->json($response, ['error' => 'Datos incompletos'], 400);
    }


public function filter(Request $request, Response $response, array $args) {
    $this->container->get('eloquent');

    $offset = filter_var($args['offset'] ?? 0, FILTER_VALIDATE_INT);
    $limit = filter_var($args['limit'] ?? 20, FILTER_VALIDATE_INT);

    if($offset === false || $offset < 0 || $limit === false || $limit < 1 || $limit > 100) {
        return $this->json($response, 
        ['error' => 'El offset debe ser mayor o igual a 0 y el limite debe estar entre 1 y 100'],
            422);

    }
    $params = $request->getQueryParams();
    $name = trim((string) ($params['nombre'] ?? $params['nombre_completo'] ?? ''));
    $cedula = trim((string) ($params['cedula'] ?? $params['cedula'] ?? ''));

    $query = PacienteModel::query()
    ->select([
        'id',
        'cedula',
        'nombre_completo',
        'fecha_nacimiento',
        'telefono'
    ]);

    if ($name !== '') {
        $query->where('pacientes.nombre_completo', 'like', "%{$name}%");
    }

    if ($cedula !== '') {
        $query->where('pacientes.cedula', 'like', "%{$cedula}%");
    }


    $total = (clone $query)->count('pacientes.id');

    $pacientes = $query
    ->orderBy('nombre_completo')
    ->offset($offset)
    ->limit($limit)
    ->get()
    ->toArray();

        return $this->json($response, [
            'data' => $pacientes,
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total
            ]
        ], 200);
    }


private function json(Response $response, array $payload, int $status) : Response {
        $response->getBody()->write(json_encode($payload,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response 
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }

}