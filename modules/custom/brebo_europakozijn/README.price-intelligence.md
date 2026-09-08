# Europakozijn Price Intelligence v1

This first version predicts observed supplier/configurator gross prices from calibrated examples. It deliberately keeps BREBO purchasing conditions separate from technical pricing.

## Current calibrated scope

- Aluplast Ideal 4000: simple fixed and tilt-turn frames around the controlled 1200 x 1200 reference.
- Aluplast Ideal 7000 NL Blockprofiel: simple fixed and tilt-turn frames using same-run controlled references at 1200 x 1200 and 980 x 1360.
- BREBO commercial discount: 49%, applied only after gross prediction.

## Explicitly not inferred yet

Mullions, transoms, doors, HST systems, no-glass configurations and unverified option effects are not silently guessed. They must be added from structured observations and blind validation.

## Model principle

Technical configuration -> gross prediction -> uncertainty band -> BREBO discount -> expected net purchase price.
