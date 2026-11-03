import { Component, signal } from '@angular/core';
import { RouterModule, RouterOutlet } from '@angular/router';

import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatMenuModule } from '@angular/material/menu';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatListModule } from '@angular/material/list';
import { MatDividerModule } from '@angular/material/divider';

type MenuItem = {
  label : string;
  icon : string;
  route : string;
  rol : number[];
}

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterModule, MatToolbarModule, MatIconModule, MatButtonModule,
    MatTooltipModule, MatMenuModule, MatSidenavModule, MatListModule, MatDividerModule
  ],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  protected readonly title = signal('Presentation');

  menuItems: MenuItem[] = [
    {label: 'Inicio', icon: 'home', route:'home', rol :[0, 1 , 2, 3]},
    {label: 'Medicos', icon: 'stethoscope', route:'medico', rol : [0, 1, 3]},
    {label: 'Pacientes', icon: 'personal_injury', route:'paciente', rol :[0, 1]},
    {label: 'Citas', icon: 'calendar_month', route:'cita', rol :[0, 1]},
  ];

  logout() {

  }

  loggedIn() : boolean {
    return true;
  }
}