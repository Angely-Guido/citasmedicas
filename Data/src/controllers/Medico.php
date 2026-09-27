<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response; // objeto para enviar la respuesta al usuario
use Psr\Http\Message\ServerRequestInterface as Request; //objeto que contiene toda la informacion de lo que el usuario nos pidio
use Psr\Container\ContainerInterface; //molde oficial para poder recibir nuestra caja de heramientas
use App\Models\Medico as MedicoModel;
use App\Models\User;
use Illuminate\Database\QueryException;

class Medico {
    private ContainerInterface $container; // Variable para guardar la caja de herramientas

     //Cuando alguien visite la URL asignada, Slim por debajo va a crear el objeto
     //Medico y le inyecta la caja de herramientas que configuramos en el inicio.
    public function __construct(ContainerInterface $c) {
        $this->container = $c; // Toma la caja $c y se guarda en la variable $container para poder usarla en toda la clase.
    }

    /*
    En este metodo Slim detecta que necesita tres cosas y se las inyecta (inyeccion por metodo)
    $request: lo que el usuario manda
    $response: El lienzo en blanco para contestar
    $args:Cualquier variable extra que venga en la URL
   */
    public function read(Request $request, Response $response, array $args) {
        $this->container->get('eloquent');
        if (isset($args['id'])) {
        $medicos = MedicoModel::query()
            ->select(['medicos.id', 'especialidad_id', 'especialidades.nombre as nombre_especialidad' ,'nombre_completo', 'licencia', 'telefono'])
            ->join('especialidades', 'medicos.especialidad_id', '=', 'especialidades.id')
            ->find($args['id']);

        return $this->json($response, ['data' => $medicos], 200);

        }

        $medicos = MedicoModel::query()
            ->select(['id', 'especialidad_id', 'nombre_completo', 'licencia', 'telefono'])
            ->get()
            ->toArray();

        return $this->json($response, ['data' => $medicos], 200);

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
    $name = trim((string) ($params['nombre'] ?? $params['nombre_completo']));
    $license = trim((string) ($params['licencia'] ?? $params['cedula']));
    $specialty = trim((string) ($params['especialidad'] ?? null));

    $query = MedicoModel::query()
    ->select([
        'medicos.id',
        'medicos.nombre_completo',
        'medicos.especialidad_id',
        'especialidades.nombre as especialidad_nombre',
        'medicos.licencia',
        'medicos.telefono'
    ])
    ->join('especialidades', 'medicos.especialidad_id', '=', 'especialidades.id');

    if ($name !== '') {
        $query->where('medicos.nombre_completo', 'like', "%{$name}%");
    }

    if ($license !== '') {
        $query->where('medicos.licencia', 'like', "%{$license}%");
    }

    if ($specialty !== '') {
        $query->where('especialidades.nombre', 'like', "%{$specialty}%");
    }

    $total = (clone $query)->count('medicos.id');

    $medicos = $query
    ->orderBy('medicos.nombre_completo')
    ->offset($offset)
    ->limit($limit)
    ->get()
    ->toArray();

        return $this->json($response, [
            'data' => $medicos,
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total
            ]
        ], 200);
    }


    public function create(Request $request, Response $response, array $args): Response {
        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];
        //return $this->json($response, ['recibido' => $data], 200);

        $errors = [];
        $specialtyId = filter_var($data['especialidad_id'] ?? null, FILTER_VALIDATE_INT);
        $fullName = trim((string) ($data['nombre_completo'] ?? ''));
        $license = trim((string) ($data['licencia'] ?? ''));
        $phone = isset($data['telefono']) ? trim((string) $data['telefono']) : null;
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($specialtyId === false || $specialtyId === null || $specialtyId < 1) {
            $errors['especialidad_id'] = 'Debe ser un entero positivo.';
        }
        if ($fullName === '' || mb_strlen($fullName) > 150) {
            $errors['nombre_completo'] = 'Es obligatorio y debe tener máximo 150 caracteres.';
        }
        if ($license === '' || mb_strlen($license) > 50) {
            $errors['licencia'] = 'Es obligatoria y debe tener máximo 50 caracteres.';
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
            $medico = $eloquent->connection()->transaction(
                static function () use ($specialtyId, $fullName, $license, $phone, $username, $password): MedicoModel {
                    $roleId = User::query()
                        ->from('roles')
                        ->where('nombre', 'Médico')
                        ->value('id');

                    if ($roleId === null) {
                        throw new RuntimeException('El rol Médico no está configurado.');
                    }

                    $user = User::create([
                        'username' => $username,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'rol_id' => $roleId,
                        'activo' => true,
                    ]);

                    return MedicoModel::create([
                        'usuario_id' => $user->id,
                        'especialidad_id' => $specialtyId,
                        'nombre_completo' => $fullName,
                        'licencia' => $license,
                        'telefono' => $phone,
                    ]);
                }
            );
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                $message = (string) ($exception->errorInfo[2] ?? '');

                if (str_contains($message, 'username')) {
                    return $this->json($response, ['error' => 'El nombre de usuario ya está registrado.'], 409);
                }

                return $this->json($response, ['error' => 'La licencia ya está registrada.'], 409);
            }

            throw $exception;
        }

        return $this->json($response, ['data' => $medico->toArray()], 201);
    }






public function update(Request $request, Response $response, array $args): Response {
        // Implementation for updating a Medico record would go here.
        $eloquent = $this->container->get('eloquent');
        if (isset($args['id'])) {
            $medico = MedicoModel::find($args['id']);

            if ($medico === null) {
                return $this->json($response, ['error' => 'Médico no encontrado.'], 404);
            }

            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];

            if (isset($data['nombre_completo'])) {
                $fullName = trim((string) $data['nombre_completo']);
                
                if ($fullName === '' || mb_strlen($fullName) > 150) {
                    return $this->json($response, ['error' => 'Nombre completo inválido.'], 422);
                }
                $medico->nombre_completo = $fullName;
            }

            if (isset($data['telefono'])) {
                $phone = trim((string) $data['telefono']);

                if ($phone !== null && mb_strlen($phone) > 20) {
                    $errors['telefono'] = 'Debe tener máximo 20 caracteres.';
                    return $this->json($response, ['error' => 'Telefono invalido.'], 422);
                }
                $medico->telefono = $phone;
            }

            if (isset($data['especialidad_id'])) {
                $specialtyId = filter_var($data['especialidad_id'] ?? null, FILTER_VALIDATE_INT);

                if ($specialtyId === false || $specialtyId === null || $specialtyId < 1) {
                    return $this->json($response, ['error' => 'Especialidad invalido.'], 422);
                }

                $medico->especialidad_id = $specialtyId;
            }

            if (isset($data['licencia'])) {
                $license = trim((string) $data['licencia']);

                if ($license === '' || mb_strlen($license) > 50) {
                    return $this->json($response, ['error' => 'Licencia invalido.'], 422);
                }
                
                $medico->licencia = $license;
            }

            $medico->save();

            return $this->json($response, ['data' => $medico->toArray()], 200);
            }
    }


public function delete(Request $request, Response $response, array $args): Response
    {
        $eloquent = $this->container->get('eloquent');
        if (isset($args['id'])) {
            $medico = MedicoModel::find($args['id']);

            if ($medico === null) {
                return $this->json($response, ['error' => 'Meedico no encontrado.'], 404);
            }

            $citasCount = $eloquent->table('citas')
                ->where('medico_id', $medico->id)
                ->count();

            if ($citasCount > 0) {
                return $this->json($response, ['error' => 'No se puede eliminar al medico porque tiene citas asignadas'], 409);
            }

            $eloquent->connection()->transaction(static function () use ($medico): void {
                $usuarioId = $medico->usuario_id;

                $medico->delete();

                if ($usuarioId !== null) {
                    $usuario = User::find($usuarioId);
                    if ($usuario !== null) {
                        $usuario->delete();
                    }
                }
            });

            return $this->json($response, ['message' => 'Medico eliminado exitosamente'], 200);
        }
    }

private function json(Response $response, array $payload, int $status) : Response {
        $response->getBody()->write(json_encode($payload,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response // Luego se le coloca un encabezo con metadatos, como el tipo de contenido y un codigo de estado y tipo.
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }

}