# Contributing

Contribuições são bem-vindas! Este documento estabelece as diretrizes para contribuir com o Hosts Manager.

## Código de Conduta

Ao participar deste projeto, você concorda em manter um ambiente respeitoso e colaborativo. Comportamento tóxico, assédio ou discriminação não serão tolerados.

## Como Contribuir

### 1. Reportar Bugs

Abra uma [Issue](https://github.com/viniciuswerneck/virtual-hosts-manager/issues/new?template=bug_report.md) com:

- Versão do PHP, Apache, Laravel e sistema operacional
- Passos para reproduzir
- Comportamento esperado vs. observado
- Logs ou screenshots relevantes

### 2. Sugerir Funcionalidades

Abra uma [Feature Request](https://github.com/viniciuswerneck/virtual-hosts-manager/issues/new?template=feature_request.md) descrevendo:

- O problema que a funcionalidade resolve
- Como você imagina a solução
- Exemplos de uso

### 3. Pull Requests

#### Fluxo

1. **Fork** o repositório
2. Crie uma branch descritiva: `feat/meu-recurso`, `fix/meu-bug`
3. Faça as alterações seguindo o [Code Style](AGENTS.md#code-style)
4. Escreva ou atualize **testes**
5. Execute `composer run test` e `./vendor/bin/pint --test`
6. Abra o **Pull Request** para `master`

#### Conventional Commits

Todas as mensagens de commit devem seguir:

```
tipo(escopo): descrição no imperativo
```

**Tipos**: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`

**Exemplos**:
```
feat(auth): add OAuth login
fix(api): correct authentication timeout
docs(readme): add installation section
test(controller): add tests for batch operations
```

#### Checklist do PR

- [ ] Testes passam (`composer run test`)
- [ ] Lint aprovado (`./vendor/bin/pint --test`)
- [ ] Código segue o style guide (PSR-12)
- [ ] Nenhuma senha ou chave exposta no código
- [ ] CHANGELOG.md atualizado
- [ ] Documentação atualizada (README, docs/)

### 4. Desenvolvimento Local

```bash
composer run dev
# http://localhost:8000
```

### 5. Testes

```bash
composer run test                    # Todos os testes
php artisan test --filter test_name  # Teste específico
./vendor/bin/pint                    # Verificar code style
```

## Estrutura de Branches

| Branch | Uso |
|---|---|
| `master` | Release atual, sempre estável |
| `*.x` | Manutenção de versões anteriores |
| `feat/*` | Novas funcionalidades |
| `fix/*` | Correções de bugs |
| `docs/*` | Documentação |
| `refactor/*` | Refatoração |

## Dúvidas?

Abra uma [Discussion](https://github.com/viniciuswerneck/virtual-hosts-manager/discussions) — estamos felizes em ajudar!
