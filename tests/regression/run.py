"""Run isolated regressions against an untouched upstream checkout; never load its .env.
Requires upstream's Laravel 10 dependencies and kyslik/column-sortable, PHP OpenSSL/SQLite,
OpenSSL CLI on PATH and Python 3. Every HTTP request is faked.
"""
import argparse
import hashlib
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--upstream', type=Path, required=True)
parser.add_argument('--vendor-autoload', type=Path, required=True)
parser.add_argument('--php', default='php')
parser.add_argument('--php-ini', type=Path)
parser.add_argument('--output', type=Path, default=Path('jangopay-test-results'))
args = parser.parse_args()
source = Path(__file__).resolve().parents[2]
output = args.output.resolve()
output.mkdir(parents=True, exist_ok=True)
php = [args.php] + (['-c', str(args.php_ini.resolve())] if args.php_ini else []) + ['-d', 'display_errors=1']
env = dict(os.environ, JANGOPAY_VENDOR_AUTOLOAD=str(args.vendor_autoload.resolve()))
results = []

def run(label, cmd, test_env=None):
    result = subprocess.run(cmd, env=test_env or env, capture_output=True, text=True, encoding='utf-8', errors='replace', timeout=60)
    log = result.stdout + result.stderr
    (output / (label.replace('/', '_') + '.log')).write_text(log, encoding='utf-8')
    results.append({'check': label, 'exit_code': result.returncode, 'result': log.strip()})
    if result.returncode:
        raise RuntimeError(label + ': ' + log[-2500:])
    return result

def snapshot(root):
    return {f.relative_to(root).as_posix(): hashlib.sha256(f.read_bytes()).hexdigest()
            for f in root.rglob('*') if f.is_file()}

try:
    with tempfile.TemporaryDirectory(prefix='jangopay-regression-') as temp:
        temp = Path(temp)
        patched = temp / 'patched'
        shutil.copytree(args.upstream.resolve(), patched, ignore=shutil.ignore_patterns('.git', 'vendor', 'node_modules', '.env', '*.log'))
        patch_cmd = [sys.executable, str(source / 'patch/apply_patch.py'), str(patched)]
        run('patch-first-application', patch_cmd)
        before = snapshot(patched)
        run('patch-second-application', patch_cmd)
        assert snapshot(patched) == before, 'patch must be byte-identical on rerun'
        # A missing final-language anchor must abort before any unrelated file changes.
        broken = temp / 'broken'
        shutil.copytree(args.upstream.resolve(), broken, ignore=shutil.ignore_patterns('.git', 'vendor', 'node_modules', '.env', '*.log'))
        drift = broken / 'resources/lang/ja/user.php'
        drift.write_text(drift.read_text(encoding='utf-8').replace("\n    'payment' => [\n", "\n    'payment_changed' => [\n"), encoding='utf-8')
        before = snapshot(broken)
        rejected = subprocess.run([sys.executable, str(source / 'patch/apply_patch.py'), str(broken)], capture_output=True, text=True, encoding='utf-8')
        assert rejected.returncode != 0 and snapshot(broken) == before, 'source drift must fail before writes'
        results.append({'check': 'patch-drift-no-writes', 'exit_code': 0})
        conflict = temp / 'conflict'
        shutil.copytree(args.upstream.resolve(), conflict, ignore=shutil.ignore_patterns('.git', 'vendor', 'node_modules', '.env', '*.log'))
        (conflict / 'config/jangopay.php').write_text('<?php // existing operator file\n', encoding='utf-8')
        before = snapshot(conflict)
        rejected = subprocess.run([sys.executable, str(source / 'patch/apply_patch.py'), str(conflict)], capture_output=True, text=True, encoding='utf-8')
        assert rejected.returncode != 0 and snapshot(conflict) == before, 'destination conflict must fail before writes'
        results.append({'check': 'patch-conflict-no-writes', 'exit_code': 0})
        env['JANGOPAY_UPSTREAM'] = str(patched)
        # Lint all adapter/test files and modified upstream files.
        original = snapshot(args.upstream.resolve())
        changed = [f for f in patched.rglob('*.php')
                   if hashlib.sha256(f.read_bytes()).hexdigest() != original.get(f.relative_to(patched).as_posix())]
        for f in changed:
            run('lint-' + f.relative_to(patched).as_posix(), php + ['-l', str(f)])
        tests = source / 'tests'
        run('offline-crypto', php + [str(tests / 'offline/test_gateway.php')])
        run('http-fx', php + [str(tests / 'regression/http_fx.php')])
        run('merchant-contract', php + [str(tests / 'regression/merchant_contract.php')])
        run('native-flow', php + [str(tests / 'regression/native_flow.php')])
        run('checkout-rendering', php + [str(tests / 'regression/checkout.php')])
        run('readiness-working', php + [str(tests / 'regression/readiness.php')])
        run('readiness-disabled-process', php + ['-d', 'disable_functions=proc_open', str(tests / 'regression/readiness.php'), 'disabled-process'])
        no_cli = dict(env, PATH='')
        run('readiness-missing-cli', php + [str(tests / 'regression/readiness.php'), 'missing-cli'], no_cli)
    print('PASS isolated regression suite')
finally:
    (output / 'summary.json').write_text(json.dumps(results, ensure_ascii=False, indent=2), encoding='utf-8')
