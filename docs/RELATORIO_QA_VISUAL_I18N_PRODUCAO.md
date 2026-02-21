# Relatório de QA Visual: Produção

Este teste foi realizado com sucesso.

## Adendo (2026-02-21)
- Reteste direcionado para `tr`, `zh`, `ar` usando a rota `/p/helber`.
- Resultado: Home e Booking Page (sufixo de duracao) aprovados para esses idiomas.

## Reteste completo (2026-02-21)
- Ajuste de rota: Booking Page validada em `/<locale>/p/helber` (antes estava em `/<locale>/helber`).
- Resultado: todos os idiomas revalidados sem ocorrencias de "NÃO ENCONTRADO" na Home ou no sufixo de duracao da Booking Page.
- Status geral: ✅ OK para `pt-br`, `pt`, `es`, `fr`, `de`, `it`, `ja`, `ko`, `ru`, `zh-cn`.

## Nota de consolidacao
- Qualquer "NÃO ENCONTRADO" listado abaixo refere-se ao teste inicial com rota incorreta e deve ser considerado **superado** pelo reteste completo.

## Idioma: `/pt-br`

### 1) Home (desktop)
- **Exibido**: "Ativar cobranca no agendamento"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/pt-br/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_pt-br_desktop.png)

### 1) Home (mobile)
- **Exibido**: "Ativar cobranca no agendamento"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/pt-br/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_pt-br_mobile.png)

---

## Idioma: `/pt`

### 1) Home (desktop)
- **Exibido**: "Ativar cobranca no agendamento"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/pt/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_pt_desktop.png)

### 1) Home (mobile)
- **Exibido**: "Ativar cobranca no agendamento"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/pt/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_pt_mobile.png)

---

## Idioma: `/es`

### 1) Home (desktop)
- **Exibido**: "Activar cobro en la reserva"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/es/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_es_desktop.png)

### 1) Home (mobile)
- **Exibido**: "Activar cobro en la reserva"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/es/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_es_mobile.png)

---

## Idioma: `/fr`

### 1) Home (desktop)
- **Exibido**: "Activer le paiement à la réservation"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/fr/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_fr_desktop.png)

### 1) Home (mobile)
- **Exibido**: "Activer le paiement à la réservation"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/fr/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_fr_mobile.png)

---

## Idioma: `/de`

### 1) Home (desktop)
- **Exibido**: "Zahlung bei Buchung aktivieren"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/de/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_de_desktop.png)

### 1) Home (mobile)
- **Exibido**: "Zahlung bei Buchung aktivieren"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/de/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_de_mobile.png)

---

## Idioma: `/it`

### 1) Home (desktop)
- **Exibido**: "Attiva il pagamento alla prenotazione"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/it/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_it_desktop.png)

### 1) Home (mobile)
- **Exibido**: "Attiva il pagamento alla prenotazione"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/it/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_it_mobile.png)

---

## Idioma: `/ja`

### 1) Home (desktop)
- **Exibido**: "予約時の支払いを有効化"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/ja/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_ja_desktop.png)

### 1) Home (mobile)
- **Exibido**: "予約時の支払いを有効化"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/ja/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_ja_mobile.png)

---

## Idioma: `/ko`

### 1) Home (desktop)
- **Exibido**: "예약 시 결제 활성화"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/ko/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_ko_desktop.png)

### 1) Home (mobile)
- **Exibido**: "예약 시 결제 활성화"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/ko/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_ko_mobile.png)

---

## Idioma: `/ru`

### 1) Home (desktop)
- **Exibido**: "Включить оплату при бронировании"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/ru/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_ru_desktop.png)

### 1) Home (mobile)
- **Exibido**: "Включить оплату при бронировании"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/ru/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_ru_mobile.png)

---

## Idioma: `/tr`

### 1) Home (desktop)
- **Exibido**: "Rezervasyonda ödemeyi etkinleştir"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "15 dk"
- **Status**: ✅ OK

### 1) Home (mobile)
- **Exibido**: "Rezervasyonda ödemeyi etkinleştir"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "15 dk"
- **Status**: ✅ OK

---

## Idioma: `/zh`

### 1) Home (desktop)
- **Exibido**: "启用预约时付款"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "15 分钟"
- **Status**: ✅ OK

### 1) Home (mobile)
- **Exibido**: "启用预约时付款"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "15 分钟"
- **Status**: ✅ OK

---

## Idioma: `/zh-cn`

### 1) Home (desktop)
- **Exibido**: "启用预约时付款"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/zh-cn/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_zh-cn_desktop.png)

### 1) Home (mobile)
- **Exibido**: "启用预约时付款"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "NÃO ENCONTRADO"
- **Status**: ❌ ERRO
- **URL**: https://meetrix.opentshost.com/zh-cn/helber
- **Evidência**: ![Erro Booking](C:/Users/helbe/.gemini/antigravity/brain/f60e9bb1-218f-44e0-a103-62293507934c/booking_zh-cn_mobile.png)

---

## Idioma: `/ar`

### 1) Home (desktop)
- **Exibido**: "تفعيل الدفع عند الحجز"
- **Status**: ✅ OK

### 2) Account (desktop)
- **Status**: ✅ OK (sem chaves hardcoded)

### 3) Booking Page (desktop)
- **Exibido (Sufixo)**: "15 دقيقة"
- **Status**: ✅ OK

### 1) Home (mobile)
- **Exibido**: "تفعيل الدفع عند الحجز"
- **Status**: ✅ OK

### 3) Booking Page (mobile)
- **Exibido (Sufixo)**: "15 دقيقة"
- **Status**: ✅ OK

---


### Validação QA Visual – Automação (Desktop + Mobile)

- **PT-BR**: OK
- **PT**: OK
- **ES**: OK
- **FR**: OK
- **DE**: OK
- **IT**: OK
- **JA**: OK
- **KO**: OK
- **RU**: OK
- **ZH-CN**: OK
