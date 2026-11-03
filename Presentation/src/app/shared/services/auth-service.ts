import { inject, Service, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment.development';
import { TokenService } from './token-service';
import { Router } from '@angular/router';
import { User } from '../models/User';
import { IToken, LoginForm } from '../models/interfaces';
import { tap, map, catchError, of } from 'rxjs';

const servidor = environment.servidor + '/auth';

@Service()
export class AuthService {
    private http = inject(HttpClient);
    private readonly srvTokens = inject(TokenService);
    private readonly router = inject(Router);
    public userActualS = signal(new User);
    public login(datos : LoginForm) {
        //console.log(datos);
        return this.http.post<IToken>(`${servidor}/login` , datos)
        .pipe(
            tap((tokens) => {
                //console.log(tokens);
                this.doLogin(tokens);
            }),
            map(() => true),
            catchError((error) => {
                console.log(error);
                return of (error.status);
            })
        )
    }

    doLogin(tokens : any) {
        //guardar los tokens
        this.srvTokens.setTokens(tokens);
        //actualizar datos globales para usuario y rol
        this.userActualS.set(this.userActual);
    }

    public isLoggedIn() : boolean {
        return !!this.srvTokens.token && !this.srvTokens.jwtTokenExp();
    }

    private get userActual() : User {
        if(!this.srvTokens.token) {
            return new User();
        } else {
            const tokenD = this.srvTokens.decodeToken();
            return new User({id: tokenD.sub, username: tokenD.username, rol_id: tokenD.rol_id })
        }
    }
}