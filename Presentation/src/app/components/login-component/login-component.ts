import { Component, inject, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { FrmLogin } from '../forms/frm-login/frm-login';
import { LoginForm } from '../../shared/models/interfaces';
import { AuthService } from '../../shared/services/auth-service';
import { Router } from '@angular/router';

@Component({
  imports: [],
  selector: 'app-login-component',
  styleUrl: './login-component.css',
  templateUrl: './login-component.html',
})
export class LoginComponent implements OnInit {
  private readonly dialogo = inject(MatDialog);
  private srvAuth = inject(AuthService);
  private readonly router = inject(Router);

  private mostrarDialogo() {
    const dialogRef = this.dialogo.open(FrmLogin, {
      width:'100%',
      maxWidth: '400px',
      disableClose: true
    });
    dialogRef.afterClosed().subscribe((datosLogin) => {
      if (datosLogin === undefined || datosLogin == false) {
        return;
      }
      this.login(datosLogin);
    });
  }

  login(datos : LoginForm) {
    this.srvAuth.login(datos)
      .subscribe({
        next: (res) => {
          if (res && res != 401) {
            this.router.navigate(['/home']);
          }
        },
        error: () => {
          // this.mostrarError.set(true);
        }
      })
  }

  ngOnInit(): void {
    this.mostrarDialogo();
  }
}