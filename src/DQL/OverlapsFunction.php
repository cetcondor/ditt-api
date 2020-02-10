<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * overlaps ::= "overlaps" "(" YYYY-MM-DDTHH:ii:ss+00:00, YYYY-MM-DDTHH:ii:ss+00:00, startTimecolumn, endTimeColumn ")"
 */
class OverlapsFunction extends FunctionNode
{
    /**
     * @var InputParameter
     */
    private $startTime;

    /**
     * @var InputParameter
     */
    private $endTime;

    /**
     * @var string
     */
    private $startTimeColumn;

    /**
     * @var string
     */
    private $endTimeColumn;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->startTime = $parser->InParameter();
        $parser->match(Lexer::T_COMMA);
        $this->endTime = $parser->InParameter();
        $parser->match(Lexer::T_COMMA);
        $this->startTimeColumn = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->endTimeColumn = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            '(((%s)::timestamp, (%s)::timestamp) OVERLAPS (%s, %s))',
            $this->startTime->dispatch($sqlWalker),
            $this->endTime->dispatch($sqlWalker),
            $this->startTimeColumn->dispatch($sqlWalker),
            $this->endTimeColumn->dispatch($sqlWalker)
        );
    }
}
