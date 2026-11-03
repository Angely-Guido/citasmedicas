import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { TokenService } from '../../services/token-service';

export const jwtInterceptor: HttpInterceptorFn = (req, next) => {
  const token = inject(TokenService).token;
  if (token) {
    req = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    })
  }
  return next(req);
};