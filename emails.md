# Textos de E-mails do Sistema

Este documento contém todos os textos de e-mails que o sistema pode enviar, já traduzidos para português.

---

## 1. Redefinição de Senha

**Assunto:** Notificação de Redefinição de Senha

**Corpo do e-mail:**

> Olá!
>
> Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para a sua conta.
>
> **[ Redefinir Senha ]** *(botão)*
>
> Este link de redefinição de senha expira em 60 minutos.
>
> Se você não solicitou a redefinição de senha, nenhuma ação adicional é necessária.
>
> Atenciosamente,
> RestSaas

**Rodapé:**

> Se você estiver tendo problemas ao clicar no botão "Redefinir Senha", copie e cole a URL abaixo no seu navegador:
> [link de redefinição]

---

## 2. Verificação de E-mail (atualmente desativado)

> Nota: Este e-mail só será enviado se `MustVerifyEmail` for ativado no model `User`.

**Assunto:** Verificar Endereço de E-mail

**Corpo do e-mail:**

> Olá!
>
> Por favor, clique no botão abaixo para verificar seu endereço de e-mail.
>
> **[ Verificar Endereço de E-mail ]** *(botão)*
>
> Se você não criou uma conta, nenhuma ação adicional é necessária.
>
> Atenciosamente,
> RestSaas

**Rodapé:**

> Se você estiver tendo problemas ao clicar no botão "Verificar Endereço de E-mail", copie e cole a URL abaixo no seu navegador:
> [link de verificação]

---

## Páginas relacionadas a e-mail (views Blade)

### Página: Verificar E-mail (`auth/verify.blade.php`)

- Título: **Verifique seu Endereço de E-mail**
- Mensagem de sucesso: "Um novo link de verificação foi enviado para o seu endereço de e-mail."
- Instrução: "Antes de continuar, por favor verifique seu e-mail para encontrar o link de verificação."
- Texto auxiliar: "Se você não recebeu o e-mail, **clique aqui para solicitar outro**."

### Página: Solicitar Redefinição de Senha (`auth/passwords/email.blade.php`)

- Título: **Redefinir Senha**
- Campo: Endereço de E-mail
- Botão: **Enviar Link de Redefinição de Senha**

### Página: Redefinir Senha (`auth/passwords/reset.blade.php`)

- Título: **Redefinir Senha**
- Campos: Endereço de E-mail, Senha, Confirmar Senha
- Botão: **Redefinir Senha**
